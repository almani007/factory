<?php

namespace Tenancy\Traits;

trait HasTenant
{
    public static function bootHasTenant(): void
    {
        static::addGlobalScope('tenant', function ($builder) {
            $tenantId = config('tenancy.current_tenant_id');

            if ($tenantId) {
                $builder->where('tenant_id', $tenantId);
            }
        });
    }

    public function scopeForTenant($builder, int $tenantId)
    {
        return $builder->where('tenant_id', $tenantId);
    }
}
