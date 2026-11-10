<?php

namespace App\Modules\Costing\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use App\Modules\Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionCost extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'production_order_id',
        'material_cost',
        'labour_cost',
        'machine_cost',
        'overhead_cost',
        'wastage_cost',
        'rework_cost',
        'total_cost',
        'unit_cost',
        'quantity',
        'costed_at',
    ];

    protected $casts = [
        'material_cost' => 'decimal:4',
        'labour_cost' => 'decimal:4',
        'machine_cost' => 'decimal:4',
        'overhead_cost' => 'decimal:4',
        'wastage_cost' => 'decimal:4',
        'rework_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'quantity' => 'decimal:4',
        'costed_at' => 'datetime',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\ProductionOrder::class);
    }
}