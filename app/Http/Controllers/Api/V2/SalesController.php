<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id ?? 0;

        $orders = DB::table('sales_orders as so')
            ->where('so.tenant_id', $tenantId)
            ->select(['so.id', 'so.so_number', 'so.status', 'so.order_date', 'so.total_amount'])
            ->orderByDesc('so.order_date')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $orders]);
    }
}
