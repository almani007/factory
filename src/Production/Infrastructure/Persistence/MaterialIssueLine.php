<?php

namespace Production\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialIssueLine extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'material_issue_id',
        'tenant_id',
        'item_id',
        'item_variant_id',
        'quantity',
        'unit_id',
        'warehouse_id',
        'bin_id',
        'batch_id',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(MaterialIssue::class);
    }
}