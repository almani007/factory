<?php

namespace Production\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrder extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_MATERIAL_RESERVED = 'material_reserved';
    const STATUS_IN_PRODUCTION = 'in_production';
    const STATUS_QUALITY_INSPECTION = 'quality_inspection';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CLOSED = 'closed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'order_number',
        'sales_order_id',
        'bom_header_id',
        'bom_version_id',
        'routing_version_id',
        'planned_quantity',
        'actual_quantity',
        'good_quantity',
        'rejected_quantity',
        'rework_quantity',
        'scrap_quantity',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'status',
        'priority',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:4',
        'actual_quantity' => 'decimal:4',
        'good_quantity' => 'decimal:4',
        'rejected_quantity' => 'decimal:4',
        'rework_quantity' => 'decimal:4',
        'scrap_quantity' => 'decimal:4',
        'planned_start_date' => 'datetime',
        'planned_end_date' => 'datetime',
        'actual_start_date' => 'datetime',
        'actual_end_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'production_order_id');
    }

    public function bomVersion(): BelongsTo
    {
        return $this->belongsTo(\Manufacturing\Models\BOMVersion::class, 'bom_version_id');
    }
}