<?php

namespace App\Modules\Costing\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandardCost extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'item_id',
        'effective_from',
        'effective_to',
        'material_cost',
        'labour_cost',
        'machine_cost',
        'overhead_cost',
        'total_cost',
        'is_active',
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