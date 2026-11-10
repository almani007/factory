<?php

namespace Dispatch\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dispatch - outbound shipment for delivery.
 */
class Dispatch extends Model
{
    protected $table = 'dispatches';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id', 'factory_id', 'sales_order_id', 'dispatch_number',
        'dispatch_date', 'vehicle_details', 'driver_name', 'status', 'notes',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'vehicle_details' => 'array',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(DispatchLine::class);
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(\Sales\Infrastructure\Persistence\SalesOrder::class);
    }
}