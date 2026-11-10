<?php

namespace Production\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FinishedGoodsReceipt - quality-approved finished goods received into warehouse.
 */
class FinishedGoodsReceipt extends Model
{
    protected $table = 'finished_goods_receipts';

    protected $fillable = [
        'tenant_id', 'factory_id', 'production_order_id', 'warehouse_id',
        'receipt_number', 'receipt_date', 'quantity', 'unit_id',
        'batch_id', 'stock_movement_id', 'received_by', 'notes',
    ];

    protected $casts = [
        'receipt_date' => 'datetime',
        'quantity' => 'decimal:4',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Organisation\Infrastructure\Persistence\Warehouse::class);
    }
}