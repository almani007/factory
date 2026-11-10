<?php

namespace App\Modules\Tenancy\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use App\Modules\Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Factory extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'name',
        'code',
        'address',
        'city',
        'country',
        'timezone',
        'working_hours',
        'shifts',
        'is_active',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'shifts' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function workCentres(): HasMany
    {
        return $this->hasMany(WorkCentre::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }
}