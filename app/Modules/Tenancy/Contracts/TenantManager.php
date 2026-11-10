<?php

namespace App\Modules\Tenancy\Contracts;

interface TenantManager
{
    public function setCurrentTenant(object $tenant): void;
    public function getCurrentTenant(): ?object;
    public function getCurrentTenantId(): ?int;
    public function clearCurrentTenant(): void;
}