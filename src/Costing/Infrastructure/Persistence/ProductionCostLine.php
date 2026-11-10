<?php

namespace Costing\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductionCostLine - cost component breakdown for audit trail.
 */
class ProductionCostLine extends Model
{
    protected $table = 'production_cost_lines';

    protected $fillable = [
        'production_cost_id', 'tenant_id', 'cost_component',
        'amount', 'source_type', 'source_id', 'description',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
    ];

    public function cost(): BelongsTo
    {
        return $this->belongsTo(ProductionCost::class);
    }
}