<?php

namespace Organisation\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'name',
        'start_time',
        'end_time',
        'break_minutes',
        'is_active',
    ];

    protected $casts = [
        'break_minutes' => 'integer',
        'is_active' => 'boolean',
    ];
}