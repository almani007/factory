<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;

class HealthController extends Controller
{
    public function check()
    {
        $db = app('db');
        try {
            $db->connection()->getPdo();
            $database = 'connected';
        } catch (\Exception $e) {
            $database = 'error: '.$e->getMessage();
        }

        return response()->json([
            'status' => $database === 'connected' ? 'healthy' : 'degraded',
            'database' => $database,
            'timestamp' => now()->toISOString(),
        ]);
    }
}