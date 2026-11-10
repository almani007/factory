<?php

namespace Manufacturing\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BOMOperation extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'bom_version_id',
        'tenant_id',
        'sequence',
        'operation_name',
        'work_centre_id',
        'labour_required_minutes',
        'machine_required_minutes',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'labour_required_minutes' => 'integer',
        'machine_required_minutes' => 'integer',
    ];
}