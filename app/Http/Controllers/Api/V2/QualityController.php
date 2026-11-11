<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QualityController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id ?? 0;

        $inspections = DB::table('quality_inspections as qi')
            ->where('qi.tenant_id', $tenantId)
            ->select(['qi.id', 'qi.inspection_number', 'qi.inspection_type', 'qi.status', 'qi.inspected_date', 'qi.description'])
            ->orderByDesc('qi.inspected_date')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $inspections]);
    }
}
