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

        $notifications = [];
        $unreadCount = 0;
        if ($user) {
            try {
                $tenantIdForNotif = $tenant?->id ?? ($user->tenant_id ?? 0);
                $rows = DB::table('notifications')
                    ->where('tenant_id', $tenantIdForNotif)
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get();

                $notifications = $rows->map(function ($n) {
                    $arr = (array) $n;
                    $arr['body'] = $arr['body'] ?? $arr['message'] ?? '';
                    $arr['is_read'] = !empty($arr['read_at']) || !empty($arr['is_read']);
                    $arr['data'] = is_string($arr['data'] ?? null) ? (json_decode($arr['data'], true) ?: []) : ($arr['data'] ?? []);
                    $arr['created_at'] = !empty($arr['created_at']) ? \Illuminate\Support\Carbon::parse($arr['created_at'])->format('d M Y, H:i') : '';
                    $arr['actor_name'] = $arr['actor_name'] ?? 'System';
                    $arr['type'] = $arr['type'] ?? 'info';
                    unset($arr['message']);
                    return $arr;
                })->values()->all();

                $unreadCount = (int) DB::table('notifications')
                    ->where('tenant_id', $tenantIdForNotif)
                    ->whereNull('read_at')
                    ->count();
            } catch (\Throwable $e) {
                $notifications = [];
                $unreadCount = 0;
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
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
}
