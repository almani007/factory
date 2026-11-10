<?php

namespace App\Shared\Support;

use Carbon\Carbon;

class TenantContext
{
    public static function currentTenantId(): ?int
    {
        $tenant = app('tenant');
        return $tenant ? $tenant->getCurrentTenantId() : null;
    }

    public static function currentFactoryId(): ?int
    {
        return request('factory_id') ?? request()->header('X-Factory-ID');
    }

    public static function currentUserId(): ?int
    {
        return auth()->id();
    }

    public static function now(): Carbon
    {
        return Carbon::now(config('app.timezone', 'Asia/Karachi'));
    }
}