<?php

namespace Inventory\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StockReservation
 *
 * Reserves stock for a document (production order, sales order).
 * Active reservations reduce Available quantity.
 */
class StockReservation extends Model
{
    protected $table = 'stock_reservations';

    protected $fillable = [
        'tenant_id', 'factory_id', 'warehouse_id', 'item_id',
        'item_variant_id', 'reservation_type', 'reference_document_type',
        'reference_document_id', 'quantity', 'unit_id', 'reserved_until',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'reserved_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(\Products\Infrastructure\Persistence\Item::class);
    }
}