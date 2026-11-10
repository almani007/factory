<?php

namespace Production\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionEntryOutput extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'production_entry_id',
        'tenant_id',
        'item_id',
        'quantity',
        'unit_id',
        'batch_id',
        'warehouse_id',
        'bin_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ProductionEntry::class);
    }
}