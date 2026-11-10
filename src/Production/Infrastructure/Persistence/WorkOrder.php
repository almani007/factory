<?php

namespace Production\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrder extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'production_order_id',
        'work_centre_id',
        'machine_id',
        'operator_id',
        'operation_name',
        'planned_quantity',
        'actual_quantity',
        'good_quantity',
        'rejected_quantity',
        'started_at',
        'completed_at',
        'downtime_minutes',
        'status',
        'notes',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:4',
        'actual_quantity' => 'decimal:4',
        'good_quantity' => 'decimal:4',
        'rejected_quantity' => 'decimal:4',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'downtime_minutes' => 'integer',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }
}