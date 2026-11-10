<?php

namespace Accounting\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingBook extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'base_currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}