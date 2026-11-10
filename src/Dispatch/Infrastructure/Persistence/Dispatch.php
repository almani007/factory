<?php

namespace Dispatch\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispatch extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'sales_order_id',
        'dispatch_number',
        'dispatch_date',
        'vehicle_details',
        'driver_name',
        'status',
        'notes',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'vehicle_details' => 'array',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(DispatchLine::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(\Sales\Models\SalesOrder::class);
    }
}