<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CostingController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id ?? 0;

        $costs = DB::table('production_costs as pc')
            ->where('pc.tenant_id', $tenantId)
            ->select(['pc.id', 'pc.item_id', 'pc.material_cost', 'pc.labour_cost', 'pc.machine_cost', 'pc.overhead_cost', 'pc.total_cost'])
            ->orderByDesc('pc.created_at')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $costs]);
    }
}
