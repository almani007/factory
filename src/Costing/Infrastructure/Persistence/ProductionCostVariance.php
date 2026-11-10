<?php

namespace Costing\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

/**
 * ProductionCostVariance - actual vs standard cost variance with explanation.
 */
class ProductionCostVariance extends Model
{
    protected $table = 'production_cost_variances';

    protected $fillable = [
        'tenant_id', 'production_order_id', 'standard_cost', 'actual_cost',
        'variance_amount', 'variance_percentage', 'material_variance',
        'labour_variance', 'machine_variance', 'overhead_variance',
        'variance_explanation', 'calculated_at',
    ];

    protected $casts = [
        'standard_cost' => 'decimal:4',
        'actual_cost' => 'decimal:4',
        'variance_amount' => 'decimal:4',
        'variance_percentage' => 'decimal:4',
        'material_variance' => 'decimal:4',
        'labour_variance' => 'decimal:4',
        'machine_variance' => 'decimal:4',
        'overhead_variance' => 'decimal:4',
        'calculated_at' => 'datetime',
    ];
}