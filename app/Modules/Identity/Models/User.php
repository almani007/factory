<?php

namespace App\Modules\Identity\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, HasTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
        'email_verified_at',
        'mfa_enabled',
        'mfa_secret',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'mfa_enabled' => 'boolean',
    ];

    public function factories()
    {
        return $this->belongsToMany(
            \App\Modules\Tenancy\Models\Factory::class,
            'user_factory_access',
            'user_id',
            'factory_id'
        )->withPivot(['permissions', 'is_active']);
    }

    public function hasFactoryAccess(int $factoryId): bool
    {
        return $this->factories()->where('factory_id', $factoryId)->where('is_active', true)->exists();
    }
}