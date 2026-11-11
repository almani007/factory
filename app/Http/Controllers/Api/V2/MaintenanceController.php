<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id ?? 0;

        $machines = DB::table('machines as m')
            ->where('m.tenant_id', $tenantId)
            ->select(['m.id', 'm.machine_code', 'm.name', 'm.status', 'm.location', 'm.last_maintenance_date'])
            ->orderBy('m.machine_code')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $machines]);
    }
}
