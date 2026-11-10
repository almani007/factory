<?php

namespace Inventory\Application\Services;

use Inventory\Infrastructure\Persistence\StockMovement;
use Inventory\Infrastructure\Persistence\StockMovementLine;
use Inventory\Infrastructure\Persistence\StockBalance;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * StockMovementService
 *
 * Implements the immutable inventory ledger. Every stock change creates a
 * traceable stock ledger entry. Posted movements cannot be silently edited
 * or deleted. Stock balances are a controlled performance projection.
 *
 * Business rules:
 *   BR-003 Posted inventory movements cannot be silently edited or deleted.
 *   BR-004 Stock cannot become negative unless explicitly allowed by policy.
 *   BR-010 Financial and inventory transactions must be atomic.
 */
class StockMovementService
{
    /**
     * Post a stock movement atomically.
     *
     * @param array $dto {
     *     tenant_id, factory_id, warehouse_id, movement_type, reference,
     *     transaction_date, posting_date, user_id, reason,
     *     lines: array of { item_id, item_variant_id, batch_id, warehouse_id,
     *                        bin_id, quantity, unit_id, unit_cost }
     * }
     * @return StockMovement
     * @throws InsufficientStockException
     */
    public function postMovement(array $dto): StockMovement
    {
        return DB::transaction(function () use ($dto) {
            // Validate OUT movements against available stock
            if ($this->isOutboundMovement($dto['movement_type'])) {
                $this->validateAvailability($dto);
            }

            // Create the movement header
            $movement = StockMovement::create([
                'tenant_id' => $dto['tenant_id'],
                'factory_id' => $dto['factory_id'] ?? null,
                'warehouse_id' => $dto['warehouse_id'],
                'movement_type' => $dto['movement_type'],
                'reference' => $dto['reference'],
                'source_document_type' => $dto['source_document_type'] ?? null,
                'source_document_id' => $dto['source_document_id'] ?? null,
                'source_document_line' => $dto['source_document_line'] ?? null,
                'transaction_date' => $dto['transaction_date'],
                'posting_date' => $dto['posting_date'],
                'user_id' => $dto['user_id'],
                'reason' => $dto['reason'] ?? null,
                'posted_at' => now(),
                'posted_by' => $dto['user_id'],
            ]);

            $totalCost = 0;

            // Create movement lines and update balances
            foreach ($dto['lines'] as $line) {
                $quantity = (float) $line['quantity'];
                $unitCost = (float) ($line['unit_cost'] ?? 0);
                $lineTotal = $quantity * $unitCost;

                StockMovementLine::create([
                    'movement_id' => $movement->id,
                    'tenant_id' => $dto['tenant_id'],
                    'item_id' => $line['item_id'],
                    'item_variant_id' => $line['item_variant_id'] ?? null,
                    'batch_id' => $line['batch_id'] ?? null,
                    'warehouse_id' => $line['warehouse_id'] ?? $dto['warehouse_id'],
                    'bin_id' => $line['bin_id'] ?? null,
                    'quantity' => $quantity,
                    'unit_id' => $line['unit_id'] ?? null,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineTotal,
                ]);

                $totalCost += $lineTotal;

                // Update stock balance projection (with row-level lock)
                $this->updateStockBalance(
                    $dto['tenant_id'],
                    $dto['factory_id'] ?? null,
                    $line['warehouse_id'] ?? $dto['warehouse_id'],
                    $line['item_id'],
                    $line['item_variant_id'] ?? null,
                    $line['batch_id'] ?? null,
                    $quantity,
                    $dto['movement_type']
                );
            }

            // Update total cost on movement
            $movement->total_cost = $totalCost;
            $movement->save();

            Log::info('Stock movement posted', [
                'movement_id' => $movement->id,
                'tenant_id' => $dto['tenant_id'],
                'movement_type' => $dto['movement_type'],
                'reference' => $dto['reference'],
                'user_id' => $dto['user_id'],
            ]);

            return $movement->load('lines.item');
        }, 3); // retry on deadlock/serialization failure
    }

    /**
     * Reverse a posted stock movement with a compensating transaction.
     *
     * @param int $movementId
     * @param string $reason
     * @param int $userId
     * @return StockMovement
     */
    public function reverseMovement(int $movementId, string $reason, int $userId): StockMovement
    {
        return DB::transaction(function () use ($movementId, $reason, $userId) {
            $original = StockMovement::with('lines')->findOrFail($movementId);

            if ($original->posted_at === null) {
                throw new \LogicException('Cannot reverse a movement that has not been posted.');
            }

            // Build reversal lines (opposite sign)
            $reversalLines = [];
            foreach ($original->lines as $line) {
                $reversalLines[] = [
                    'item_id' => $line->item_id,
                    'item_variant_id' => $line->item_variant_id,
                    'batch_id' => $line->batch_id,
                    'warehouse_id' => $line->warehouse_id,
                    'bin_id' => $line->bin_id,
                    'quantity' => (float) $line->quantity,
                    'unit_id' => $line->unit_id,
                    'unit_cost' => (float) $line->unit_cost,
                ];
            }

            $reversal = StockMovement::create([
                'tenant_id' => $original->tenant_id,
                'factory_id' => $original->factory_id,
                'warehouse_id' => $original->warehouse_id,
                'movement_type' => $this->getReverseMovementType($original->movement_type),
                'reference' => 'REV-' . $original->reference,
                'source_document_type' => 'stock_movement',
                'source_document_id' => $original->id,
                'transaction_date' => now(),
                'posting_date' => now(),
                'user_id' => $userId,
                'reason' => $reason,
                'reversal_reference_id' => $original->id,
                'posted_at' => now(),
                'posted_by' => $userId,
            ]);

            foreach ($reversalLines as $line) {
                $reversal->lines()->create($line);

                // Update balance with opposite sign
                $reverseType = $this->getReverseMovementType($original->movement_type);
                $this->updateStockBalance(
                    $original->tenant_id,
                    $original->factory_id,
                    $line['warehouse_id'],
                    $line['item_id'],
                    $line['item_variant_id'],
                    $line['batch_id'],
                    $line['quantity'],
                    $reverseType
                );
            }

            Log::info('Stock movement reversed', [
                'original_id' => $original->id,
                'reversal_id' => $reversal->id,
                'reason' => $reason,
                'user_id' => $userId,
            ]);

            return $reversal->load('lines.item');
        }, 3);
    }

    /**
     * Get available quantity for an item in a warehouse.
     *
     * Available = OnHand - Reserved
     */
    public function getAvailableQuantity(
        int $tenantId,
        ?int $factoryId,
        int $warehouseId,
        string $itemId,
        ?string $itemVariantId = null,
        ?string $batchId = null
    ): float {
        $query = StockBalance::where('tenant_id', $tenantId)
            ->where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId);

        if ($factoryId) {
            $query->where('factory_id', $factoryId);
        }
        if ($itemVariantId) {
            $query->where('item_variant_id', $itemVariantId);
        }
        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        $balance = $query->first();

        if (!$balance) {
            return 0.0;
        }

        return (float) $balance->on_hand - (float) $balance->reserved;
    }

    /**
     * Reserve stock for a document (e.g., production order or sales order).
     */
    public function reserveStock(array $dto): \Inventory\Infrastructure\Persistence\StockReservation
    {
        $available = $this->getAvailableQuantity(
            $dto['tenant_id'],
            $dto['factory_id'] ?? null,
            $dto['warehouse_id'],
            $dto['item_id'],
            $dto['item_variant_id'] ?? null,
            $dto['batch_id'] ?? null
        );

        if ($available < (float) $dto['quantity']) {
            throw new InsufficientStockException(
                "Insufficient stock for item {$dto['item_id']} in warehouse {$dto['warehouse_id']}. "
                . "Available: {$available}, Requested: {$dto['quantity']}"
            );
        }

        return \Inventory\Infrastructure\Persistence\StockReservation::create([
            'tenant_id' => $dto['tenant_id'],
            'factory_id' => $dto['factory_id'] ?? null,
            'warehouse_id' => $dto['warehouse_id'],
            'item_id' => $dto['item_id'],
            'item_variant_id' => $dto['item_variant_id'] ?? null,
            'reservation_type' => $dto['reservation_type'],
            'reference_document_type' => $dto['reference_document_type'] ?? null,
            'reference_document_id' => $dto['reference_document_id'] ?? null,
            'quantity' => $dto['quantity'],
            'unit_id' => $dto['unit_id'] ?? null,
            'reserved_until' => $dto['reserved_until'] ?? null,
            'is_active' => true,
        ]);
    }

    /**
     * Release a stock reservation.
     */
    public function releaseReservation(string $reservationId): void
    {
        \Inventory\Infrastructure\Persistence\StockReservation::where('id', $reservationId)
            ->update(['is_active' => false]);
    }

    /**
     * Validate that OUT movements have sufficient available stock.
     */
    private function validateAvailability(array $dto): void
    {
        foreach ($dto['lines'] as $line) {
            $available = $this->getAvailableQuantity(
                $dto['tenant_id'],
                $dto['factory_id'] ?? null,
                $line['warehouse_id'] ?? $dto['warehouse_id'],
                $line['item_id'],
                $line['item_variant_id'] ?? null,
                $line['batch_id'] ?? null
            );

            if ($available < (float) $line['quantity']) {
                throw new InsufficientStockException(
                    "Insufficient stock for item {$line['item_id']}. "
                    . "Available: {$available}, Requested: {$line['quantity']}"
                );
            }
        }
    }

    /**
     * Update stock balance projection with row-level locking.
     */
    private function updateStockBalance(
        int $tenantId,
        ?int $factoryId,
        int $warehouseId,
        string $itemId,
        ?string $itemVariantId,
        ?string $batchId,
        float $quantity,
        string $movementType
    ): void {
        $isInbound = $this->isInboundMovement($movementType);

        StockBalance::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'item_variant_id' => $itemVariantId,
                'batch_id' => $batchId,
            ],
            [
                'on_hand' => 0,
                'reserved' => 0,
                'in_transit' => 0,
                'unit_id' => null,
                'last_movement_at' => now(),
            ]
        );

        // Lock and update
        $balance = StockBalance::where('tenant_id', $tenantId)
            ->where('factory_id', $factoryId)
            ->where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->where('item_variant_id', $itemVariantId)
            ->where('batch_id', $batchId)
            ->lockForUpdate()
            ->first();

        if ($isInbound) {
            $balance->on_hand = bcadd((string) $balance->on_hand, (string) $quantity, 4);
        } else {
            $balance->on_hand = bcsub((string) $balance->on_hand, (string) $quantity, 4);
        }

        $balance->last_movement_at = now();
        $balance->save();
    }

    private function isInboundMovement(string $type): bool
    {
        $inbound = [
            'goods_receipt', 'material_return', 'production_output',
            'finished_goods_receipt', 'stock_transfer_in', 'stock_adjustment',
            'customer_return',
        ];
        return in_array($type, $inbound);
    }

    private function isOutboundMovement(string $type): bool
    {
        $outbound = [
            'goods_issue', 'material_issue', 'stock_transfer_out',
            'stock_adjustment', 'stock_damage', 'stock_scrap', 'supplier_return',
        ];
        return in_array($type, $outbound);
    }

    private function getReverseMovementType(string $type): string
    {
        $map = [
            'goods_receipt' => 'goods_issue',
            'goods_issue' => 'goods_receipt',
            'material_issue' => 'material_return',
            'material_return' => 'material_issue',
            'production_output' => 'stock_adjustment',
            'finished_goods_receipt' => 'stock_adjustment',
            'stock_transfer_in' => 'stock_transfer_out',
            'stock_transfer_out' => 'stock_transfer_in',
            'stock_damage' => 'stock_adjustment',
            'stock_scrap' => 'stock_adjustment',
        ];
        return $map[$type] ?? 'stock_adjustment';
    }
}