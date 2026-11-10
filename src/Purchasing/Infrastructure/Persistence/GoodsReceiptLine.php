<?php

namespace Purchasing\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptLine extends Model
{
    protected $table = 'goods_receipt_lines';

    protected $fillable = [
        'goods_receipt_id', 'tenant_id', 'purchase_order_line_id', 'item_id',
        'quantity', 'unit_id', 'batch_id', 'stock_movement_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function poLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLine::class, 'purchase_order_line_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\Products\Infrastructure\Persistence\Item::class);
    }
}