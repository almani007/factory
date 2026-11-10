<?php

namespace Tenancy\Services;

use Tenancy\Contracts\TenantManager as TenantManagerContract;
use Tenancy\Infrastructure\Persistence\Tenant;

class TenantManager implements TenantManagerContract
{
    protected ?Tenant $tenant = null;

    protected ?int $factoryId = null;

    public function setCurrentTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->factoryId = null;
    }

    public function currentTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function currentTenantId(): ?int
    {
        return $this->tenant?->id;
    }

    public function setCurrentFactoryId(?int $factoryId): void
    {
        $this->factoryId = $factoryId;
    }

    public function currentFactoryId(): ?int
    {
        return $this->factoryId;
    }

    public function run(Tenant $tenant, callable $callback): mixed
    {
        $previousTenant = $this->tenant;
        $previousFactory = $this->factoryId;

        $this->tenant = $tenant;
        $this->factoryId = null;

        try {
            return $callback($tenant);
        } finally {
            $this->tenant = $previousTenant;
            $this->factoryId = $previousFactory;
        }
    }

    public function forget(): void
    {
        $this->tenant = null;
        $this->factoryId = null;
    }
}
