<?php

namespace Tenancy\Traits;

use Shared\Support\TenantContext;

trait HasTenant
{
    public static function bootHasTenant(): void
    {
        static::addGlobalScope('tenant', function ($builder) {
            $tenantId = TenantContext::currentTenantId();

            if ($tenantId) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
            }
        });

        static::creating(function ($model) {
            if (! $model->tenant_id) {
                $model->tenant_id = TenantContext::currentTenantId();
            }
        });
    }

    public function scopeForTenant($builder, int $tenantId)
    {
        return $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
    }
}
