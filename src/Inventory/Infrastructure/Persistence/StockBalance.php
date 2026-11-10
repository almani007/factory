<?php

namespace Inventory\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StockBalance
 *
 * Controlled performance projection of stock. Not the source of truth —
 * stock_movements is. Available = OnHand - Reserved.
 */
class StockBalance extends Model
{
    protected $table = 'stock_balances';

    protected $fillable = [
        'tenant_id', 'factory_id', 'warehouse_id', 'item_id',
        'item_variant_id', 'batch_id', 'on_hand', 'reserved',
        'in_transit', 'unit_id', 'last_movement_at',
    ];

    protected $casts = [
        'on_hand' => 'decimal:4',
        'reserved' => 'decimal:4',
        'in_transit' => 'decimal:4',
        'last_movement_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(\Products\Infrastructure\Persistence\Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Organisation\Infrastructure\Persistence\Warehouse::class);
    }

    /**
     * Available quantity = OnHand - Reserved (BR-004).
     */
    public function getAvailableQuantityAttribute(): float
    {
        return (float) $this->on_hand - (float) $this->reserved;
    }
}