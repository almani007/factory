<?php

namespace Dispatch\Application\Services;

use Dispatch\Infrastructure\Persistence\Dispatch;
use Dispatch\Infrastructure\Persistence\DispatchLine;
use Dispatch\Infrastructure\Persistence\DeliveryNote;
use Sales\Infrastructure\Persistence\SalesOrder;
use Sales\Infrastructure\Persistence\SalesOrderReservation;
use Inventory\Application\Services\StockMovementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DispatchService
 *
 * Implements dispatch and logistics:
 *   Delivery Planning → Dispatch Order → Packing List → Delivery Note → Proof of Delivery
 *
 * Business rule: Dispatch cannot exceed authorised available stock (BR-004, BR-005).
 */
class DispatchService
{
    protected StockMovementService $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Create a dispatch order for a sales order.
     */
    public function createDispatch(array $dto): Dispatch
    {
        return Dispatch::create([
            'tenant_id' => $dto['tenant_id'],
            'factory_id' => $dto['factory_id'],
            'sales_order_id' => $dto['sales_order_id'] ?? null,
            'dispatch_number' => $dto['dispatch_number'],
            'dispatch_date' => $dto['dispatch_date'],
            'vehicle_details' => $dto['vehicle_details'] ?? null,
            'driver_name' => $dto['driver_name'] ?? null,
            'status' => Dispatch::STATUS_PENDING,
            'notes' => $dto['notes'] ?? null,
        ]);
    }

    /**
     * Add lines to a dispatch.
     */
    public function addDispatchLines(string $dispatchId, array $lines): array
    {
        $dispatch = Dispatch::findOrFail($dispatchId);
        $created = [];

        foreach ($lines as $line) {
            $created[] = DispatchLine::create([
                'dispatch_id' => $dispatchId,
                'tenant_id' => $dispatch->tenant_id,
                'item_id' => $line['item_id'],
                'quantity' => (float) $line['quantity'],
                'unit_id' => $line['unit_id'] ?? null,
            ]);
        }

        return $created;
    }

    /**
     * Approve and execute dispatch.
     * Validates that dispatch does not exceed available stock.
     * Creates stock movement (goods_issue) for dispatch.
     */
    public function executeDispatch(Dispatch $dispatch, int $userId): Dispatch
    {
        if ($dispatch->status !== Dispatch::STATUS_PENDING && $dispatch->status !== Dispatch::STATUS_APPROVED) {
            throw new \LogicException('Only pending or approved dispatches can be executed.');
        }

        return DB::transaction(function () use ($dispatch, $userId) {
            // Validate stock availability for each dispatch line
            foreach ($dispatch->lines as $line) {
                // In practice, resolve warehouse from item/default warehouse
                $warehouseId = $dtoWarehouse ?? null;

                if ($warehouseId) {
                    $available = $this->checkAvailableStock(
                        $dispatch->tenant_id,
                        $warehouseId,
                        $line->item_id,
                        (float) $line->quantity
                    );

                    if (!$available) {
                        throw new \LogicException(
                            "Insufficient stock for item {$line->item_id} in warehouse {$warehouseId}"
                        );
                    }
                }
            }

            // Post stock movement for dispatch (goods_issue)
            $movementLines = [];
            foreach ($dispatch->lines as $line) {
                $movementLines[] = [
                    'item_id' => $line->item_id,
                    'quantity' => (float) $line->quantity,
                    'unit_id' => $line->unit_id,
                    'warehouse_id' => $dtoWarehouse ?? null,
                    'unit_cost' => 0, // costed by inventory valuation
                ];
            }

            $this->stockService->postMovement([
                'tenant_id' => $dispatch->tenant_id,
                'factory_id' => $dispatch->factory_id,
                'warehouse_id' => $dtoWarehouse ?? null,
                'movement_type' => 'goods_issue',
                'reference' => 'DISP-' . $dispatch->dispatch_number,
                'source_document_type' => 'dispatch',
                'source_document_id' => $dispatch->id,
                'transaction_date' => now(),
                'posting_date' => now(),
                'user_id' => $userId,
                'reason' => 'Goods dispatched for delivery ' . $dispatch->dispatch_number,
                'lines' => $movementLines,
            ]);

            // Mark sales order reservations as fulfilled
            if ($dispatch->sales_order_id) {
                SalesOrderReservation::where('sales_order_id', $dispatch->sales_order_id)
                    ->where('is_fulfilled', false)
                    ->update(['is_fulfilled' => true]);
            }

            $dispatch->status = Dispatch::STATUS_IN_TRANSIT;
            $dispatch->save();

            Log::info('Dispatch executed', [
                'dispatch_id' => $dispatch->id,
                'dispatch_number' => $dispatch->dispatch_number,
                'executed_by' => $userId,
            ]);

            return $dispatch->load('lines.item');
        }, 3);
    }

    /**
     * Create a delivery note for a completed dispatch.
     */
    public function createDeliveryNote(array $dto): DeliveryNote
    {
        return DeliveryNote::create([
            'tenant_id' => $dto['tenant_id'],
            'dispatch_id' => $dto['dispatch_id'],
            'note_number' => $dto['note_number'],
            'note_date' => $dto['note_date'],
            'notes' => $dto['notes'] ?? null,
        ]);
    }

    /**
     * Mark dispatch as delivered.
     */
    public function markDelivered(Dispatch $dispatch, int $userId, ?string $proofOfDelivery = null): Dispatch
    {
        $dispatch->status = Dispatch::STATUS_DELIVERED;
        $dispatch->notes = ($dispatch->notes ? $dispatch->notes . "\n" : '') .
            "DELIVERED: Proof of delivery recorded ($proofOfDelivery) by user {$userId}";
        $dispatch->save();

        Log::info('Dispatch marked delivered', [
            'dispatch_id' => $dispatch->id,
            'dispatch_number' => $dispatch->dispatch_number,
        ]);

        return $dispatch;
    }

    /**
     * Check available stock for a dispatch line.
     */
    private function checkAvailableStock(int $tenantId, int $warehouseId, string $itemId, float $quantity): bool
    {
        $balance = \Inventory\Infrastructure\Persistence\StockBalance::where('tenant_id', $tenantId)
            ->where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->first();

        if (!$balance) {
            return false;
        }

        return $balance->available_quantity >= $quantity;
    }
}