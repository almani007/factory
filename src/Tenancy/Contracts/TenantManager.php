<?php

namespace Tenancy\Contracts;

use Tenancy\Infrastructure\Persistence\Tenant;

/**
 * Holds the active tenant/factory for the current request or job.
 *
 * A tenant is always resolved from trusted server-side context (the
 * authenticated user or an explicit platform action), never from a
 * tenant_id supplied by the browser.
 */
interface TenantManager
{
    public function setCurrentTenant(?Tenant $tenant): void;

    public function currentTenant(): ?Tenant;

    public function currentTenantId(): ?int;

    public function setCurrentFactoryId(?int $factoryId): void;

    public function currentFactoryId(): ?int;

    public function run(Tenant $tenant, callable $callback): mixed;

    public function forget(): void;
}
