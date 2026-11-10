<?php

namespace App\Modules\Integrations\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationConnection extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'provider',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];
}