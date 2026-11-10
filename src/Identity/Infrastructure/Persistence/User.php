<?php

namespace Identity\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasTenant;

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
        'role',
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
            \Tenancy\Models\Factory::class,
            'user_factory_access',
            'user_id',
            'factory_id'
        )->withPivot(['permissions', 'is_active']);
    }

    public function hasFactoryAccess(int $factoryId): bool
    {
        return $this->factories()->where('factory_id', $factoryId)->where('is_active', true)->exists();
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->rolePermissions();
        return in_array($permission, $permissions, true);
    }

    public function rolePermissions(): array
    {
        $rolePermissions = [
            'super_admin' => [
                'view_tenants', 'edit_tenants', 'delete_tenants',
                'view_users', 'edit_users', 'delete_users',
                'view_factories', 'edit_factories', 'delete_factories',
                'view_products', 'edit_products', 'delete_products',
                'view_inventory', 'edit_inventory', 'delete_inventory',
                'view_purchasing', 'edit_purchasing', 'delete_purchasing',
                'view_production', 'edit_production', 'delete_production',
                'view_quality', 'edit_quality', 'delete_quality',
                'view_costing', 'edit_costing', 'delete_costing',
                'view_sales', 'edit_sales', 'delete_sales',
                'view_dispatch', 'edit_dispatch', 'delete_dispatch',
                'view_accounting', 'edit_accounting', 'delete_accounting',
                'view_reports', 'edit_reports', 'delete_reports',
                'view_settings', 'edit_settings', 'delete_settings',
            ],
            'factory_manager' => [
                'view_factories', 'edit_factories',
                'view_products', 'edit_products',
                'view_inventory', 'edit_inventory',
                'view_purchasing', 'edit_purchasing',
                'view_production', 'edit_production',
                'view_quality', 'edit_quality',
                'view_costing', 'view_sales', 'view_dispatch',
                'view_accounting', 'view_reports',
            ],
            'production_manager' => [
                'view_production', 'edit_production',
                'view_inventory', 'edit_inventory',
                'view_purchasing', 'edit_purchasing',
                'view_quality', 'edit_quality',
            ],
            'production_supervisor' => [
                'view_production', 'edit_production',
                'view_inventory', 'edit_inventory',
            ],
            'store_manager' => [
                'view_inventory', 'edit_inventory',
                'view_purchasing', 'edit_purchasing',
            ],
            'storekeeper' => [
                'view_inventory', 'edit_inventory',
            ],
            'purchase_manager' => [
                'view_purchasing', 'edit_purchasing',
                'view_suppliers', 'edit_suppliers',
            ],
            'quality_manager' => [
                'view_quality', 'edit_quality',
            ],
            'accountant' => [
                'view_accounting', 'edit_accounting',
                'view_sales', 'view_dispatch',
            ],
            'sales_manager' => [
                'view_sales', 'edit_sales',
                'view_dispatch', 'edit_dispatch',
                'view_customers', 'edit_customers',
            ],
            'maintenance_manager' => [
                'view_maintenance', 'edit_maintenance',
            ],
            'employee' => [
                'view_production',
            ],
            'auditor' => [
                'view_inventory', 'view_production', 'view_sales', 'view_accounting', 'view_quality',
            ],
        ];

        return $rolePermissions[$this->role] ?? [];
    }
}