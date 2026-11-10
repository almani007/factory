<?php

namespace Tenancy\Infrastructure\Provider;

use Illuminate\Support\ServiceProvider;
use Tenancy\Contracts\TenantManager;
use Tenancy\Services\TenantManager as TenantManagerImplementation;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantManager::class, fn () => new TenantManagerImplementation());
        $this->app->alias(TenantManager::class, 'tenant');
    }

    public function boot(): void
    {
        //
    }
}
