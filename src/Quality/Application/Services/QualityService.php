<?php

namespace Quality\Application\Services;

use Quality\Infrastructure\Persistence\QualityInspection;
use Quality\Infrastructure\Persistence\InspectionTemplate;
use Quality\Infrastructure\Persistence\QualityInspectionResult;
use Production\Infrastructure\Persistence\FinishedGoodsReceipt;
use Production\Infrastructure\Persistence\ProductionOrder;
use Inventory\Application\Services\StockMovementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * QualityService
 *
 * Manages quality inspections and the release gate:
 *   Finished goods must not become available for sale until required quality
 *   checks are passed or an authorised exception is recorded (BR-007).
 *
 * Quality statuses: Pending, Passed, Failed, Partially Accepted,
 *   Rework Required, Rejected.
 */
class QualityService
{
    protected StockMovementService $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Create a quality inspection from a template.
     */
    public function createInspection(array $dto): QualityInspection
    {
        return QualityInspection::create([
            'tenant_id' => $dto['tenant_id'],
            'factory_id' => $dto['factory_id'],
            'inspection_template_id' => $dto['inspection_template_id'],
            'inspection_type' => $dto['inspection_type'], // incoming, in_process, final
            'reference_type' => $dto['reference_type'], // production_order, goods_receipt, etc.
            'reference_id' => $dto['reference_id'],
            'status' => QualityInspection::STATUS_PENDING,
            'inspected_by' => $dto['inspected_by'] ?? null,
            'notes' => $dto['notes'] ?? null,
        ]);
    }

    /**
     * Record inspection results (pass/fail per checklist item).
     */
    public function recordResults(string $inspectionId, array $results, int $userId): QualityInspection
    {
        $inspection = QualityInspection::findOrFail($inspectionId);

        if ($inspection->status !== QualityInspection::STATUS_PENDING) {
            throw new \LogicException('Inspection already processed.');
        }

        return DB::transaction(function () use ($inspection, $results, $userId) {
            $allPassed = true;
            $anyFailed = false;

            foreach ($results as $result) {
                $passed = (bool) ($result['result'] === 'pass');

                QualityInspectionResult::create([
                    'quality_inspection_id' => $inspection->id,
                    'tenant_id' => $inspection->tenant_id,
                    'template_item_id' => $result['template_item_id'],
                    'measured_value' => $result['measured_value'] ?? null,
                    'result' => $passed ? 'pass' : 'fail',
                    'notes' => $result['notes'] ?? null,
                ]);

                if (!$passed) {
                    $anyFailed = true;
                    $allPassed = false;
                }
            }

            // Determine overall status
            if ($allPassed) {
                $inspection->status = QualityInspection::STATUS_PASSED;
            } elseif ($anyFailed) {
                $inspection->status = QualityInspection::STATUS_FAILED;
            }

            $inspection->inspected_by = $userId;
            $inspection->inspected_at = now();
            $inspection->save();

            Log::info('Inspection results recorded', [
                'inspection_id' => $inspection->id,
                'status' => $inspection->status,
                'inspected_by' => $userId,
            ]);

            return $inspection->load('results');
        });
    }

    /**
     * Approve or reject an inspection (quality release gate).
     * If PASSED → authorise release of finished goods to inventory.
     */
    public function approveInspection(string $inspectionId, int $approverId, ?string $comments = null): QualityInspection
    {
        $inspection = QualityInspection::findOrFail($inspectionId);

        if (!in_array($inspection->status, [QualityInspection::STATUS_PASSED, QualityInspection::STATUS_PARTIAL])) {
            throw new \LogicException('Only passed or partially accepted inspections can be approved.');
        }

        $inspection->status = QualityInspection::STATUS_PASSED;
        $inspection->approved_by = $approverId;
        $inspection->approved_at = now();

        if ($comments) {
            $inspection->notes = ($inspection->notes ? $inspection->notes . "\n" : '') .
                "Approval comments: {$comments}";
        }

        $inspection->save();

        // If this is a final inspection for finished goods, trigger release
        if ($inspection->inspection_type === 'final' &&
            $inspection->reference_type === 'production_order') {
            $this->releaseFinishedGoods($inspection, $approverId);
        }

        Log::info('Inspection approved', [
            'inspection_id' => $inspectionId,
            'approved_by' => $approverId,
            'reference_type' => $inspection->reference_type,
            'reference_id' => $inspection->reference_id,
        ]);

        return $inspection;
    }

    /**
     * Reject an inspection. Finished goods are blocked from release.
     */
    public function rejectInspection(string $inspectionId, int $rejectorId, string $reason): QualityInspection
    {
        $inspection = QualityInspection::findOrFail($inspectionId);

        $inspection->status = QualityInspection::STATUS_REJECTED;
        $inspection->notes = ($inspection->notes ? $inspection->notes . "\n" : '') .
            "REJECTED: {$reason} (by user {$rejectorId})";
        $inspection->save();

        Log::warning('Inspection rejected', [
            'inspection_id' => $inspectionId,
            'reason' => $reason,
            'rejector_id' => $rejectorId,
        ]);

        return $inspection;
    }

    /**
     * Release finished goods to inventory after quality approval.
     * Creates stock movement (finished_goods_receipt) and accounting posting.
     */
    protected function releaseFinishedGoods(QualityInspection $inspection, int $userId): void
    {
        $order = ProductionOrder::find($inspection->reference_id);
        if (!$order) {
            return;
        }

        // Find finished goods receipt for this order
        $receipt = \Production\Infrastructure\Persistence\FinishedGoodsReceipt::where(
            'production_order_id', $order->id
        )->first();

        if (!$receipt) {
            Log::warning('No finished goods receipt found for order', [
                'order_id' => $order->id,
                'inspection_id' => $inspection->id,
            ]);
            return;
        }

        // Post stock movement: finished goods received into warehouse
        $this->stockService->postMovement([
            'tenant_id' => $order->tenant_id,
            'factory_id' => $order->factory_id,
            'warehouse_id' => $receipt->warehouse_id,
            'movement_type' => 'finished_goods_receipt',
            'reference' => 'FG-' . $receipt->receipt_number,
            'source_document_type' => 'finished_goods_receipt',
            'source_document_id' => $receipt->id,
            'transaction_date' => now(),
            'posting_date' => now(),
            'user_id' => $userId,
            'reason' => 'Quality-approved finished goods received into inventory',
            'lines' => [
                [
                    'item_id' => $receipt->item_id,
                    'quantity' => (float) $receipt->quantity,
                    'unit_id' => $receipt->unit_id,
                    'batch_id' => $receipt->batch_id,
                    'warehouse_id' => $receipt->warehouse_id,
                    'bin_id' => $receipt->bin_id,
                    'unit_cost' => 0, // costed separately by costing module
                ],
            ],
        ]);

        Log::info('Finished goods released to inventory after quality approval', [
            'order_id' => $order->id,
            'receipt_id' => $receipt->id,
            'warehouse_id' => $receipt->warehouse_id,
            'released_by' => $userId,
        ]);
    }

    /**
     * Check if finished goods can be released (quality gate).
     */
    public function canReleaseFinishedGoods(string $productionOrderId): bool
    {
        $latestInspection = QualityInspection::where('reference_type', 'production_order')
            ->where('reference_id', $productionOrderId)
            ->where('inspection_type', 'final')
            ->latest('inspected_at')
            ->first();

        if (!$latestInspection) {
            return false; // No inspection = cannot release
        }

        return $latestInspection->status === QualityInspection::STATUS_PASSED;
    }

    /**
     * Record a defect.
     */
    public function recordDefect(array $dto): \Quality\Infrastructure\Persistence\QualityDefect
    {
        return \Quality\Infrastructure\Persistence\QualityDefect::create([
            'tenant_id' => $dto['tenant_id'],
            'factory_id' => $dto['factory_id'],
            'quality_inspection_id' => $dto['quality_inspection_id'] ?? null,
            'defect_category' => $dto['defect_category'],
            'defect_code' => $dto['defect_code'],
            'description' => $dto['description'],
            'quantity' => (float) $dto['quantity'],
            'unit_id' => $dto['unit_id'] ?? null,
        ]);
    }
}