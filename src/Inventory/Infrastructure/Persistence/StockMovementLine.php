<?php

namespace Inventory\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovementLine extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'movement_id',
        'tenant_id',
        'item_id',
        'item_variant_id',
        'batch_id',
        'serial_number_id',
        'warehouse_id',
        'bin_id',
        'quantity',
        'unit_id',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    public function movement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\Products\Models\Item::class);
    }
}