<?php

namespace Costing\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

/**
 * OverheadCost - manufacturing overhead allocated to production orders.
 */
class OverheadCost extends Model
{
    protected $table = 'overhead_costs';

    protected $fillable = [
        'tenant_id', 'factory_id', 'production_order_id',
        'cost_type', 'amount', 'description', 'incurred_at',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'incurred_at' => 'datetime',
    ];
}