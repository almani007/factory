<?php

namespace Shared\Support;

use Carbon\Carbon;
use Tenancy\Contracts\TenantManager;
use Tenancy\Infrastructure\Persistence\Tenant;

class TenantContext
{
    public static function manager(): TenantManager
    {
        return app(TenantManager::class);
    }

    public static function currentTenantId(): ?int
    {
        return static::manager()->currentTenantId();
    }

    public static function currentFactoryId(): ?int
    {
        return static::manager()->currentFactoryId();
    }

    public static function currentUserId(): ?int
    {
        return auth()->id();
    }

    public static function set(?Tenant $tenant): void
    {
        static::manager()->setCurrentTenant($tenant);
    }

    public static function run(Tenant $tenant, callable $callback): mixed
    {
        return static::manager()->run($tenant, $callback);
    }

    public static function forget(): void
    {
        static::manager()->forget();
    }

    public static function now(): Carbon
    {
        return Carbon::now(config('app.timezone', 'Asia/Karachi'));
    }
}
