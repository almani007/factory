<?php

namespace App\Modules\Tenancy\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'tax_id',
        'currency',
        'tax_jurisdiction',
        'timezone',
        'locale',
        'branding',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'branding' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function factories(): HasMany
    {
        return $this->hasMany(Factory::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}