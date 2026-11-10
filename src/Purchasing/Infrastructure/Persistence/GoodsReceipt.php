<?php

namespace Purchasing\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GoodsReceipt - Goods Received Note for a purchase order.
 */
class GoodsReceipt extends Model
{
    protected $table = 'goods_receipts';

    protected $fillable = [
        'tenant_id', 'factory_id', 'warehouse_id', 'purchase_order_id',
        'grn_number', 'receipt_date', 'status', 'received_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'status' => 'string',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(GoodsReceiptLine::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Organisation\Infrastructure\Persistence\Warehouse::class);
    }
}