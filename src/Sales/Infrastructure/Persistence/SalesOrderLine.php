<?php

namespace Sales\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderLine extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'sales_order_id',
        'tenant_id',
        'item_id',
        'quantity',
        'unit_id',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'total_price' => 'decimal:4',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }
}