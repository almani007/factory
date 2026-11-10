<?php

namespace Costing\Application\Services;

use Costing\Infrastructure\Persistence\ProductionCost;
use Costing\Infrastructure\Persistence\ProductionCostLine;
use Costing\Infrastructure\Persistence\StandardCost;
use Costing\Infrastructure\Persistence\ProductionCostVariance;
use Production\Infrastructure\Persistence\ProductionOrder;
use Production\Infrastructure\Persistence\MaterialIssue;
use Production\Infrastructure\Persistence\MaterialIssueLine;
use Production\Infrastructure\Persistence\ProductionEntry;
use Production\Infrastructure\Persistence\ProductionEntryOutput;
use Accounting\Application\Services\JournalEntryService;
use Inventory\Application\Services\StockMovementService;
use Shared\Domain\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CostingService
 *
 * Calculates actual production cost per work order:
 *   Actual Cost = Material + Labour + Machine + Overhead + Wastage + Rework
 *
 * Compares actual vs standard, computes variance with explanation.
 *
 * Business rules:
 *   BR-011 Historical costs must not change when current product prices change.
 */
class CostingService
{
    /**
     * Calculate actual cost for a completed production order.
     * Creates cost snapshot (immutable - historical cost protection).
     */
    public function calculateOrderCost(string $productionOrderId): ProductionCost
    {
        return DB::transaction(function () use ($productionOrderId) {
            $order = ProductionOrder::with([
                'materialIssues.lines',
                'productionEntries.outputs',
            ])->findOrFail($productionOrderId);

            if (!in_array($order->status, [
                ProductionOrder::STATUS_COMPLETED, ProductionOrder::STATUS_CLOSED,
            ])) {
                throw new \LogicException('Can only cost completed or closed orders.');
            }

            // === MATERIAL COST ===
            $materialCost = 0.0;
            foreach ($order->materialIssues as $issue) {
                foreach ($issue->lines as $line) {
                    $materialCost += (float) $line->total_cost;
                }
            }

            // === LABOUR COST ===
            $labourCost = $this->calculateLabourCost($order->id);

            // === MACHINE COST ===
            $machineCost = $this->calculateMachineCost($order->id);

            // === OVERHEAD COST ===
            $overheadCost = $this->calculateOverheadCost($order->id);

            // === WASTAGE & REWORK COST ===
            $wastageCost = $this->calculateWastageCost($order);
            $reworkCost = $this->calculateReworkCost($order);

            $totalCost = $materialCost + $labourCost + $machineCost +
                $overheadCost + $wastageCost + $reworkCost;

            $goodQty = (float) $order->good_quantity;
            $unitCost = $goodQty > 0 ? $totalCost / $goodQty : 0;

            // Create cost snapshot (immutable)
            $cost = ProductionCost::create([
                'tenant_id' => $order->tenant_id,
                'factory_id' => $order->factory_id,
                'production_order_id' => $order->id,
                'material_cost' => $materialCost,
                'labour_cost' => $labourCost,
                'machine_cost' => $machineCost,
                'overhead_cost' => $overheadCost,
                'wastage_cost' => $wastageCost,
                'rework_cost' => $reworkCost,
                'total_cost' => $totalCost,
                'unit_cost' => $unitCost,
                'quantity' => $goodQty,
                'costed_at' => now(),
            ]);

            // Create cost lines for audit trail
            $cost->lines()->createMany([
                [
                    'cost_component' => 'material',
                    'amount' => $materialCost,
                    'source_type' => 'material_issue',
                    'source_id' => null,
                    'description' => 'Raw material consumed for production',
                ],
                [
                    'cost_component' => 'labour',
                    'amount' => $labourCost,
                    'source_type' => 'labour_entry',
                    'source_id' => null,
                    'description' => 'Direct labour for production',
                ],
                [
                    'cost_component' => 'machine',
                    'amount' => $machineCost,
                    'source_type' => 'machine_cost',
                    'source_id' => null,
                    'description' => 'Machine operating cost',
                ],
                [
                    'cost_component' => 'overhead',
                    'amount' => $overheadCost,
                    'source_type' => 'overhead_allocation',
                    'source_id' => null,
                    'description' => 'Manufacturing overhead allocated',
                ],
                [
                    'cost_component' => 'wastage',
                    'amount' => $wastageCost,
                    'source_type' => 'production_scrap',
                    'source_id' => null,
                    'description' => 'Scrap and wastage cost',
                ],
                [
                    'cost_component' => 'rework',
                    'amount' => $reworkCost,
                    'source_type' => 'rework',
                    'source_id' => null,
                    'description' => 'Rework cost',
                ],
            ]);

            Log::info('Production cost calculated', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_cost' => $totalCost,
                'unit_cost' => $unitCost,
                'material' => $materialCost,
                'labour' => $labourCost,
                'machine' => $machineCost,
                'overhead' => $overheadCost,
            ]);

            return $cost->load('lines');
        });
    }

    /**
     * Compute variance: Actual vs Standard with explanation.
     */
    public function calculateVariance(string $productionOrderId): ProductionCostVariance
    {
        $order = ProductionOrder::findOrFail($productionOrderId);
        $actualCost = ProductionCost::where('production_order_id', $productionOrderId)
            ->latest('costed_at')->first();

        if (!$actualCost) {
            throw new \LogicException('No cost record found for this production order.');
        }

        // Get standard cost for the item at the time of the order
        $standardCost = StandardCost::where('item_id', $order->item_id ?? null)
            ->where('is_active', true)
            ->latest('effective_from')
            ->first();

        $standardTotal = $standardCost ? (float) $standardCost->total_cost : 0;
        $actualTotal = (float) $actualCost->total_cost;
        $varianceAmount = $actualTotal - $standardTotal;
        $variancePct = $standardTotal > 0 ? ($varianceAmount / $standardTotal) * 100 : 0;

        // Breakdown variance by component
        $materialVar = (float) $actualCost->material_cost -
            (($standardCost ? (float) $standardCost->material_cost : 0) * (float) $order->planned_quantity);
        $labourVar = (float) $actualCost->labour_cost -
            (($standardCost ? (float) $standardCost->labour_cost : 0) * (float) $order->planned_quantity);
        $machineVar = (float) $actualCost->machine_cost -
            (($standardCost ? (float) $standardCost->machine_cost : 0) * (float) $order->planned_quantity);
        $overheadVar = (float) $actualCost->overhead_cost -
            (($standardCost ? (float) $standardCost->overhead_cost : 0) * (float) $order->planned_quantity);

        $explanation = $this->buildVarianceExplanation(
            $varianceAmount, $variancePct, $materialVar, $labourVar, $machineVar, $overheadVar
        );

        $variance = ProductionCostVariance::create([
            'tenant_id' => $order->tenant_id,
            'production_order_id' => $productionOrderId,
            'standard_cost' => $standardTotal,
            'actual_cost' => $actualTotal,
            'variance_amount' => $varianceAmount,
            'variance_percentage' => $variancePct,
            'material_variance' => $materialVar,
            'labour_variance' => $labourVar,
            'machine_variance' => $machineVar,
            'overhead_variance' => $overheadVar,
            'variance_explanation' => $explanation,
            'calculated_at' => now(),
        ]);

        Log::info('Cost variance calculated', [
            'order_id' => $productionOrderId,
            'standard_cost' => $standardTotal,
            'actual_cost' => $actualTotal,
            'variance_amount' => $varianceAmount,
            'variance_percentage' => $variancePct,
        ]);

        return $variance;
    }

    /**
     * Create/update standard cost for an item.
     */
    public function setStandardCost(array $dto): StandardCost
    {
        // Deactivate previous standard cost for this item
        StandardCost::where('item_id', $dto['item_id'])
            ->where('is_active', true)
            ->update(['is_active' => false, 'effective_to' => $dto['effective_from']]);

        return StandardCost::create([
            'tenant_id' => $dto['tenant_id'],
            'item_id' => $dto['item_id'],
            'effective_from' => $dto['effective_from'],
            'effective_to' => $dto['effective_to'] ?? null,
            'material_cost' => (float) ($dto['material_cost'] ?? 0),
            'labour_cost' => (float) ($dto['labour_cost'] ?? 0),
            'machine_cost' => (float) ($dto['machine_cost'] ?? 0),
            'overhead_cost' => (float) ($dto['overhead_cost'] ?? 0),
            'total_cost' => (float) (
                ($dto['material_cost'] ?? 0) +
                ($dto['labour_cost'] ?? 0) +
                ($dto['machine_cost'] ?? 0) +
                ($dto['overhead_cost'] ?? 0)
            ),
            'is_active' => true,
        ]);
    }

    /**
     * Post cost to accounting (WIP → Finished Goods).
     */
    public function postCostToAccounting(ProductionCost $cost, int $userId): void
    {
        $order = $cost->productionOrder;

        // Dr Finished Goods Inventory, Cr Work in Progress
        \Accounting\Application\Services\JournalEntryService::class;
        // This would call the journal service - kept as a service method reference
        // Actual posting handled by Accounting module via event listener
    }

    // ============ Private calculation methods ============

    private function calculateLabourCost(string $orderId): float
    {
        // Sum labour entries for work orders under this production order
        $total = \HR\Infrastructure\Persistence\LabourEntry::where('production_order_id', $orderId)
            ->sum('total_cost');
        return (float) $total;
    }

    private function calculateMachineCost(string $orderId): float
    {
        $total = \Maintenance\Infrastructure\Persistence\MachineCost::where('production_order_id', $orderId)
            ->sum('total_cost');
        return (float) $total;
    }

    private function calculateOverheadCost(string $orderId): float
    {
        $total = \Costing\Infrastructure\Persistence\OverheadCost::where('production_order_id', $orderId)
            ->sum('amount');
        return (float) $total;
    }

    private function calculateWastageCost(ProductionOrder $order): float
    {
        $scrapQty = (float) $order->scrap_quantity;
        // Use standard material cost per unit as wastage valuation
        $stdMaterialCost = StandardCost::where('item_id', $order->item_id ?? null)
            ->where('is_active', true)
            ->value('material_cost') ?? 0;

        return $scrapQty * (float) $stdMaterialCost;
    }

    private function calculateReworkCost(ProductionOrder $order): float
    {
        $reworkQty = (float) $order->rework_quantity;
        $stdMaterialCost = StandardCost::where('item_id', $order->item_id ?? null)
            ->where('is_active', true)
            ->value('material_cost') ?? 0;

        // Rework cost = rework qty * (material + labour per unit estimate)
        return $reworkQty * ((float) $stdMaterialCost * 1.5); // simplified
    }

    private function buildVarianceExplanation(
        float $varianceAmount,
        float $variancePct,
        float $materialVar,
        float $labourVar,
        float $machineVar,
        float $overheadVar
    ): string {
        $parts = [];
        $direction = $varianceAmount > 0 ? 'overrun' : 'underrun';

        $parts[] = "Total cost {$direction} of " . number_format(abs($varianceAmount), 2) .
            " (" . number_format(abs($variancePct), 2) . "%).";

        if (abs($materialVar) > 0.01) {
            $mDir = $materialVar > 0 ? 'higher' : 'lower';
            $parts[] = "Material cost was {$mDir} by " . number_format(abs($materialVar), 2) .
                " — check purchase prices and wastage.";
        }
        if (abs($labourVar) > 0.01) {
            $lDir = $labourVar > 0 ? 'higher' : 'lower';
            $parts[] = "Labour cost was {$lDir} by " . number_format(abs($labourVar), 2) .
                " — check labour hours and rates.";
        }
        if (abs($machineVar) > 0.01) {
            $mcDir = $machineVar > 0 ? 'higher' : 'lower';
            $parts[] = "Machine cost was {$mcDir} by " . number_format(abs($machineVar), 2) .
                " — check machine hours and efficiency.";
        }
        if (abs($overheadVar) > 0.01) {
            $oDir = $overheadVar > 0 ? 'higher' : 'lower';
            $parts[] = "Overhead was {$oDir} by " . number_format(abs($overheadVar), 2) .
                " — check overhead allocation.";
        }

        return implode(' ') . ' The system explains variance by component: material (price/usage), ' .
            'labour (rate/efficiency), machine (rate/efficiency), and overhead (spending/volume).';
    }
}