<?php

namespace Tenancy\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tenancy\Contracts\TenantManager;
use Tenancy\Infrastructure\Persistence\Tenant;

class TenantContextMiddleware
{
    public function __construct(protected TenantManager $tenants)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user() ?? auth('sanctum')->user();
        $tenant = $this->resolveTenant($request, $user);

        if ($tenant) {
            $this->tenants->setCurrentTenant($tenant);

            $factoryId = $this->resolveFactoryId($request, $user, $tenant);
            if ($factoryId) {
                $this->tenants->setCurrentFactoryId($factoryId);
            }
        }

        return $next($request);
    }

    /**
     * Resolve the tenant only from trusted server-side context.
     *
     * - The authenticated user's own tenant is authoritative.
     * - A platform super admin may explicitly act on a tenant via X-Tenant-ID.
     * - Local/demo requests may fall back to the first active tenant so the
     *   seeded console is usable without a login session.
     *
     * A tenant_id in the query string or body is never trusted.
     */
    protected function resolveTenant(Request $request, $user): ?Tenant
    {
        if ($user && $user->tenant_id) {
            return Tenant::whereKey($user->tenant_id)
                ->where('is_active', true)
                ->first();
        }

        if ($user && $user->role === 'super_admin') {
            $requested = $request->header('X-Tenant-ID');

            if ($requested && ctype_digit((string) $requested)) {
                return Tenant::whereKey((int) $requested)
                    ->where('is_active', true)
                    ->first();
            }
        }

        if (app()->environment(['local', 'testing'])) {
            return Tenant::where('is_active', true)->orderBy('id')->first();
        }

        return null;
    }

    protected function resolveFactoryId(Request $request, $user, Tenant $tenant): ?int
    {
        $requested = $request->header('X-Factory-ID');

        if (! $requested || ! ctype_digit((string) $requested)) {
            return null;
        }

        $factoryId = (int) $requested;

        if ($user && method_exists($user, 'hasFactoryAccess') && ! $user->hasFactoryAccess($factoryId)) {
            return null;
        }

        return $factoryId;
    }
}
