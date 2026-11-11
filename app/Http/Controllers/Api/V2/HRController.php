<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HRController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id ?? 0;

        $employees = DB::table('employees as e')
            ->where('e.tenant_id', $tenantId)
            ->select(['e.id', 'e.employee_code', 'e.first_name', 'e.last_name', 'e.position', 'e.status'])
            ->orderBy('e.employee_code')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $employees]);
    }
}
