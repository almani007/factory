<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id ?? 0;

        $orders = DB::table('production_orders as po')
            ->leftJoin('items as i', 'i.id', '=', 'po.item_id')
            ->where('po.tenant_id', $tenantId)
            ->select([
                'po.id', 'po.order_number', 'i.name as item_name',
                'po.status', 'po.planned_quantity', 'po.good_quantity',
                'po.planned_start_date', 'po.planned_end_date',
            ])
            ->orderByDesc('po.planned_start_date')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $orders]);
    }
}
