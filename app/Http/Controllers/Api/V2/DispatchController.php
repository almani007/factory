<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id ?? 0;

        $dispatches = DB::table('dispatches as d')
            ->where('d.tenant_id', $tenantId)
            ->select(['d.id', 'd.dispatch_number', 'd.status', 'd.dispatch_date', 'd.status'])
            ->orderByDesc('d.dispatch_date')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $dispatches]);
    }
}
