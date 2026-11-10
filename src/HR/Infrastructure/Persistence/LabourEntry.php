<?php

namespace HR\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

/**
 * LabourEntry - records labour hours and cost for costing.
 */
class LabourEntry extends Model
{
    protected $table = 'labour_entries';

    protected $fillable = [
        'tenant_id', 'factory_id', 'production_order_id',
        'work_order_id', 'employee_id', 'hours', 'rate_per_hour',
        'total_cost', 'worked_at',
    ];

    protected $casts = [
        'hours' => 'decimal:4',
        'rate_per_hour' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'worked_at' => 'datetime',
    ];
}