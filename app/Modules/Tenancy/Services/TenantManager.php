<?php

namespace App\Modules\Tenancy\Services;

use App\Modules\Tenancy\Contracts\TenantManager;
use App\Modules\Tenancy\Models\Tenant;

class TenantManager implements TenantManager
{
    protected ?object $currentTenant = null;

    public function setCurrentTenant(object $tenant): void
    {
        $this->currentTenant = $tenant;
    }

    public function getCurrentTenant(): ?object
    {
        return $this->currentTenant;
    }

    public function getCurrentTenantId(): ?int
    {
        return $this->currentTenant ? (int) $this->currentTenant->id : null;
    }

    public function clearCurrentTenant(): void
    {
        $this->currentTenant = null;
    }
}