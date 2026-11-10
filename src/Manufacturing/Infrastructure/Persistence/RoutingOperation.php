<?php

namespace Manufacturing\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutingOperation extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'routing_version_id',
        'tenant_id',
        'sequence',
        'operation_name',
        'work_centre_id',
        'setup_time_minutes',
        'run_time_per_unit_minutes',
        'machine_id',
        'is_active',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'setup_time_minutes' => 'integer',
        'run_time_per_unit_minutes' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(RoutingVersion::class);
    }

    public function workCentre(): BelongsTo
    {
        return $this->belongsTo(\Organisation\Models\WorkCentre::class);
    }
}