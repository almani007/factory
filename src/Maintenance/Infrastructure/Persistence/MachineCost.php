<?php

namespace Maintenance\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

/**
 * MachineCost - machine operating cost allocated to production orders.
 */
class MachineCost extends Model
{
    protected $table = 'machine_costs';

    protected $fillable = [
        'tenant_id', 'factory_id', 'production_order_id',
        'work_order_id', 'machine_id', 'hours', 'rate_per_hour',
        'total_cost', 'operated_at',
    ];

    protected $casts = [
        'hours' => 'decimal:4',
        'rate_per_hour' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'operated_at' => 'datetime',
    ];
}