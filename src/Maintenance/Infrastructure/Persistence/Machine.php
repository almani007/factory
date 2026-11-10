<?php

namespace Maintenance\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'machine_code',
        'name',
        'manufacturer',
        'model',
        'serial_number',
        'work_centre_id',
        'installation_date',
        'purchase_cost',
        'expected_capacity',
        'operating_status',
        'warranty_info',
        'is_active',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'purchase_cost' => 'decimal:4',
        'expected_capacity' => 'decimal:4',
        'is_active' => 'boolean',
    ];
}