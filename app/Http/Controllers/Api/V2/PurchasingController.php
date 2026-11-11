<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasingController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id ?? 0;

        $pos = DB::table('purchase_orders as po')
            ->where('po.tenant_id', $tenantId)
            ->select(['po.id', 'po.po_number', 'po.status', 'po.order_date', 'po.expected_delivery_date', 'po.total_amount'])
            ->orderByDesc('po.order_date')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $pos]);
    }
}
