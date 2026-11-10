<?php

namespace Costing\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

/**
 * StandardCost - planned/expected product cost (frozen at effective date).
 */
class StandardCost extends Model
{
    protected $table = 'standard_costs';

    protected $fillable = [
        'tenant_id', 'item_id', 'effective_from', 'effective_to',
        'material_cost', 'labour_cost', 'machine_cost', 'overhead_cost',
        'total_cost', 'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'material_cost' => 'decimal:4',
        'labour_cost' => 'decimal:4',
        'machine_cost' => 'decimal:4',
        'overhead_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'is_active' => 'boolean',
    ];
}