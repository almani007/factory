<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return null;
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $tenant = null;

        if (! $user) {
            try {
                $user = DB::table('users')->where('role', 'super_admin')->orderBy('id')->first();
                $tenant = DB::table('tenants')->where('status', 'active')->orderBy('id')->first();
            } catch (\Throwable) {
                $user = null;
            }
        }

        return array_merge(parent::share($request), [
            'appName' => config('app.name', 'Brixel FactoryOS'),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? null,
            ] : null,
            'tenant' => $tenant ? [
                'name' => $tenant->name,
                'plan' => $tenant->plan,
            ] : null,
            'notifications' => $user ? DB::table('notifications')
                ->where('tenant_id', $tenant?->id ?? 0)
                ->whereNull('read_at')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get() : [],
            'unread_count' => $user ? (int) DB::table('notifications')
                ->where('tenant_id', $tenant?->id ?? 0)
                ->whereNull('read_at')
                ->count() : 0,
        ]);
    }
}
