<?php

namespace Purchasing\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderLine extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'purchase_order_id',
        'tenant_id',
        'item_id',
        'quantity',
        'unit_id',
        'unit_price',
        'total_price',
        'received_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'total_price' => 'decimal:4',
        'received_quantity' => 'decimal:4',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}