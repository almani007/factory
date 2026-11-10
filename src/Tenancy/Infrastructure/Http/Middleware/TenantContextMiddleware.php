<?php

namespace Tenancy\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tenancy\Infrastructure\Persistence\Tenant;

class TenantContextMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            config(['tenancy.current_tenant_id' => $tenant->id]);
            config(['tenancy.current_tenant' => $tenant]);
        }

        return $next($request);
    }

    protected function resolveTenant(Request $request): ?Tenant
    {
        $subdomain = $request->getHost() ? explode('.', $request->getHost())[0] : null;
        $tenantId = $request->header('X-Tenant-ID') ?? $request->input('tenant_id');

        if ($tenantId) {
            return Tenant::find($tenantId);
        }

        if ($subdomain && $subdomain !== 'www' && $subdomain !== 'api') {
            return Tenant::where('subdomain', $subdomain)->first();
        }

        return null;
    }
}
