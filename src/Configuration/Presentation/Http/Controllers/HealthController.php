<?php

namespace Configuration\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * HealthController
 *
 * Liveness probe for the application and its backing services.
 */
class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $healthy = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => array_map(
                fn (bool $result) => $result ? 'ok' : 'failed',
                $checks
            ),
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    protected function checkDatabase(): bool
    {
        try {
            DB::select('select 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function checkCache(): bool
    {
        try {
            Redis::connection()->ping();

            return true;
        } catch (\Throwable) {
            // Cache is optional; report the app as healthy without it.
            return config('cache.default') !== 'redis';
        }
    }
}
