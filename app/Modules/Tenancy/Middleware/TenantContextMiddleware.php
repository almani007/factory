<?php

namespace App\Modules\Tenancy\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Exceptions\TenantNotFoundException;

class TenantContextMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            app('tenant')->setCurrentTenant($tenant);
            config(['tenancy.current_tenant_id' => $tenant->id]);
        }

        return $next($request);
    }

    protected function resolveTenant(Request $request): ?Tenant
    {
        // Resolve from subdomain, header, or request parameter
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