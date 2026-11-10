<?php

namespace Sales\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SalesOrderReservation - stock reservation for a sales order.
 */
class SalesOrderReservation extends Model
{
    protected $table = 'sales_order_reservations';

    protected $fillable = [
        'tenant_id', 'sales_order_id', 'warehouse_id', 'item_id',
        'quantity', 'unit_id', 'is_fulfilled',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'is_fulfilled' => 'boolean',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\Products\Infrastructure\Persistence\Item::class);
    }
}