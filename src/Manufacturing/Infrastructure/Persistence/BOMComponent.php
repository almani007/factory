<?php

namespace Manufacturing\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BOMComponent extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'bom_version_id',
        'tenant_id',
        'item_id',
        'quantity',
        'unit_id',
        'expected_wastage_percentage',
        'is_alternative',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'expected_wastage_percentage' => 'decimal:4',
        'is_alternative' => 'boolean',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(BOMVersion::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\Products\Models\Item::class);
    }
}