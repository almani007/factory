<?php

namespace Production\Application\Services;

use Production\Infrastructure\Persistence\ProductionOrder;
use Production\Infrastructure\Persistence\WorkOrder;
use Production\Infrastructure\Persistence\MaterialIssue;
use Production\Infrastructure\Persistence\MaterialIssueLine;
use Production\Infrastructure\Persistence\ProductionEntry;
use Production\Infrastructure\Persistence\FinishedGoodsReceipt;
use Inventory\Application\Services\StockMovementService;
use Manufacturing\Infrastructure\Persistence\BOMVersion;
use Manufacturing\Infrastructure\Persistence\BOMComponent;
use Shared\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shared\Support\AuditLogger;
use Shared\Support\Outbox;

/**
 * ProductionOrderService
 *
 * Implements the production order lifecycle:
 *   Draft → Pending Approval → Approved → Material Reserved → In Production
 *   → Quality Inspection → Completed → Closed
 *
 * Business rules:
 *   BR-005 Production cannot consume more material than is available or authorised.
 *   BR-006 A production order must retain its BOM version.
 *   BR-007 Finished goods must follow quality-release rules.
 */
class ProductionOrderService
{
    protected StockMovementService $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Create a production order (draft).
     */
    public function createOrder(array $dto): ProductionOrder
    {
        return ProductionOrder::create([
            'tenant_id' => $dto['tenant_id'],
            'factory_id' => $dto['factory_id'],
            'sales_order_id' => $dto['sales_order_id'] ?? null,
            'bom_header_id' => $dto['bom_header_id'],
            'bom_version_id' => $dto['bom_version_id'],
            'routing_version_id' => $dto['routing_version_id'] ?? null,
            'order_number' => $dto['order_number'],
            'planned_quantity' => (float) $dto['planned_quantity'],
            'planned_start_date' => $dto['planned_start_date'],
            'planned_end_date' => $dto['planned_end_date'],
            'status' => ProductionOrder::STATUS_DRAFT,
            'priority' => $dto['priority'] ?? 'normal',
            'notes' => $dto['notes'] ?? null,
        ]);
    }

    /**
     * Submit for approval (Draft → Pending Approval).
     */
    public function submitForApproval(ProductionOrder $order, int $userId): ProductionOrder
    {
        if ($order->status !== ProductionOrder::STATUS_DRAFT) {
            throw new \LogicException('Only draft orders can be submitted for approval.');
        }

        $order->status = ProductionOrder::STATUS_PENDING_APPROVAL;
        $order->save();

        return $order;
    }

    /**
     * Approve the production order and lock in the BOM version.
     * The approved BOM version is preserved for the life of the order.
     */
    public function approveOrder(ProductionOrder $order, int $approverId): ProductionOrder
    {
        if ($order->status !== ProductionOrder::STATUS_PENDING_APPROVAL) {
            throw new \LogicException('Only pending approval orders can be approved.');
        }

        // Validate BOM version exists and is active
        $bomVersion = BOMVersion::find($order->bom_version_id);
        if (!$bomVersion || !$bomVersion->is_active) {
            throw new \LogicException('BOM version is not active or does not exist.');
        }

        $order->status = ProductionOrder::STATUS_APPROVED;
        $order->approved_by = $approverId;
        $order->approved_at = now();
        $order->save();

        Log::info('Production order approved', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'bom_version_id' => $order->bom_version_id,
            'approved_by' => $approverId,
        ]);

        return $order->load('bomVersion');
    }

    /**
     * Reserve materials for production (Approved → Material Reserved).
     * Calculates material requirements from BOM and checks availability.
     */
    public function reserveMaterials(ProductionOrder $order, int $userId): ProductionOrder
    {
        if ($order->status !== ProductionOrder::STATUS_APPROVED) {
            throw new \LogicException('Only approved orders can have materials reserved.');
        }

        // Get BOM components for this version
        $components = BOMComponent::where('bom_version_id', $order->bom_version_id)
            ->where('is_alternative', false)
            ->get();

        $plannedQty = (float) $order->planned_quantity;

        $lines = [];
        foreach ($components as $component) {
            $requiredQty = (float) $component->quantity * $plannedQty;
            $wastageFactor = 1 + ((float) $component->expected_wastage_percentage / 100);
            $requiredWithWastage = $requiredQty * $wastageFactor;

            $lines[] = [
                'item_id' => $component->item_id,
                'item_variant_id' => null,
                'quantity' => $requiredWithWastage,
                'unit_id' => $component->unit_id,
                'warehouse_id' => $order->factory_id ? null : null, // would be resolved
                'batch_id' => null,
                'unit_cost' => 0,
            ];
        }

        // Use stock service to reserve (validates availability)
        $reservation = $this->stockService->reserveStock([
            'tenant_id' => $order->tenant_id,
            'factory_id' => $order->factory_id,
            'warehouse_id' => $dtoWarehouse ?? null, // needs warehouse context
            'item_id' => $lines[0]['item_id'] ?? null,
            'quantity' => $plannedQty,
            'reservation_type' => 'production',
            'reference_document_type' => 'production_order',
            'reference_document_id' => $order->id,
        ]);

        // In practice, we'd create MaterialReservation records per line
        // For now, mark order as material reserved
        $order->status = ProductionOrder::STATUS_MATERIAL_RESERVED;
        $order->save();

        Log::info('Materials reserved for production order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'planned_quantity' => $plannedQty,
            'reserved_by' => $userId,
        ]);

        return $order;
    }

    /**
     * Start production (Material Reserved → In Production).
     */
    public function startProduction(ProductionOrder $order, int $userId): ProductionOrder
    {
        if ($order->status !== ProductionOrder::STATUS_MATERIAL_RESERVED) {
            throw new \LogicException('Production can only start after materials are reserved.');
        }

        $order->status = ProductionOrder::STATUS_IN_PRODUCTION;
        $order->actual_start_date = now();
        $order->save();

        return $order;
    }

    /**
     * Record production output (good, rejected, rework, scrap).
     */
    public function recordOutput(array $dto): ProductionEntry
    {
        $order = ProductionOrder::findOrFail($dto['production_order_id']);

        if (!in_array($order->status, [ProductionOrder::STATUS_IN_PRODUCTION, ProductionOrder::STATUS_QUALITY_INSPECTION])) {
            throw new \LogicException('Cannot record output for order in status: ' . $order->status);
        }

        return DB::transaction(function () use ($order, $dto) {
            $entry = ProductionEntry::create([
                'tenant_id' => $order->tenant_id,
                'factory_id' => $order->factory_id,
                'production_order_id' => $order->id,
                'work_order_id' => $dto['work_order_id'] ?? null,
                'entry_date' => $dto['entry_date'] ?? now(),
                'good_quantity' => (float) ($dto['good_quantity'] ?? 0),
                'rejected_quantity' => (float) ($dto['rejected_quantity'] ?? 0),
                'rework_quantity' => (float) ($dto['rework_quantity'] ?? 0),
                'scrap_quantity' => (float) ($dto['scrap_quantity'] ?? 0),
                'operator_id' => $dto['operator_id'] ?? null,
                'machine_id' => $dto['machine_id'] ?? null,
                'shift_id' => $dto['shift_id'] ?? null,
                'notes' => $dto['notes'] ?? null,
            ]);

            // Update order cumulative quantities
            $order->actual_quantity = bcadd((string) $order->actual_quantity, (string) $entry->good_quantity, 4);
            $order->good_quantity = bcadd((string) $order->good_quantity, (string) $entry->good_quantity, 4);
            $order->rejected_quantity = bcadd((string) $order->rejected_quantity, (string) $entry->rejected_quantity, 4);
            $order->rework_quantity = bcadd((string) $order->rework_quantity, (string) $entry->rework_quantity, 4);
            $order->scrap_quantity = bcadd((string) $order->scrap_quantity, (string) $entry->scrap_quantity, 4);
            $order->save();

            Log::info('Production output recorded', [
                'entry_id' => $entry->id,
                'order_id' => $order->id,
                'good_quantity' => $entry->good_quantity,
                'rejected_quantity' => $entry->rejected_quantity,
            ]);

            // Transactional outbox + audit for the committed output.
            $tenantId = (int) ($order->tenant_id ?? 0);
            $userId = (int) ($dto['operator_id'] ?? 0);
            Outbox::record(
                'ProductionOutputRecorded',
                'production_entry',
                (int) $entry->id,
                [
                    'tenant_id' => $tenantId,
                    'production_order_id' => (int) $order->id,
                    'good_quantity' => $entry->good_quantity,
                ],
                $tenantId
            );
            AuditLogger::record(
                'production_output_recorded',
                'production_entry',
                (int) $entry->id,
                [],
                ['production_order_id' => (int) $order->id, 'good_quantity' => $entry->good_quantity],
                $tenantId,
                $userId
            );

            return $entry;
        }, 3);
    }

    /**
     * Complete production (In Production → Quality Inspection → Completed).
     * Finished goods are received into inventory only after quality approval.
     */
    public function completeProduction(ProductionOrder $order, int $userId): ProductionOrder
    {
        if ($order->status !== ProductionOrder::STATUS_QUALITY_INSPECTION) {
            throw new \LogicException('Only quality-inspected orders can be completed.');
        }

        $order->status = ProductionOrder::STATUS_COMPLETED;
        $order->actual_end_date = now();
        $order->save();

        return $order;
    }

    /**
     * Close a completed production order (Completed → Closed).
     */
    public function closeOrder(ProductionOrder $order, int $userId): ProductionOrder
    {
        if ($order->status !== ProductionOrder::STATUS_COMPLETED) {
            throw new \LogicException('Only completed orders can be closed.');
        }

        $order->status = ProductionOrder::STATUS_CLOSED;
        $order->save();

        return $order;
    }

    /**
     * Cancel a production order (with audit).
     */
    public function cancelOrder(ProductionOrder $order, int $userId, string $reason): ProductionOrder
    {
        if (in_array($order->status, [ProductionOrder::STATUS_COMPLETED, ProductionOrder::STATUS_CLOSED])) {
            throw new \LogicException('Cannot cancel a completed or closed order.');
        }

        $order->status = ProductionOrder::STATUS_CANCELLED;
        $order->notes = ($order->notes ? $order->notes . "\n" : '') . "CANCELLED: {$reason} (by user {$userId})";
        $order->save();

        return $order;
    }

    /**
     * Calculate material requirements from BOM for a given quantity.
     */
    public function calculateMaterialRequirements(string $bomVersionId, float $quantity): array
    {
        $components = BOMComponent::where('bom_version_id', $bomVersionId)
            ->where('is_alternative', false)
            ->get();

        $requirements = [];
        foreach ($components as $component) {
            $requiredQty = (float) $component->quantity * $quantity;
            $wastageFactor = 1 + ((float) $component->expected_wastage_percentage / 100);
            $requiredWithWastage = $requiredQty * $wastageFactor;
            $wastageQty = $requiredWithWastage - $requiredQty;

            $requirements[] = [
                'item_id' => $component->item_id,
                'item_name' => optional($component->item)->name ?? null,
                'unit_id' => $component->unit_id,
                'base_quantity' => $requiredQty,
                'wastage_percentage' => (float) $component->expected_wastage_percentage,
                'wastage_quantity' => $wastageQty,
                'total_required' => $requiredWithWastage,
                'is_alternative' => (bool) $component->is_alternative,
            ];
        }

        return $requirements;
    }
}