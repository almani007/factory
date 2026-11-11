<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id ?? 0;

        $data = [
            'production_today' => (float) DB::table('production_orders')
                ->where('tenant_id', $tenantId)
                ->whereDate('actual_end_date', today())
                ->whereIn('status', ['completed', 'closed'])
                ->sum('good_quantity'),
            'revenue_month' => (float) DB::table('customer_invoices')
                ->where('tenant_id', $tenantId)
                ->where('status', 'paid')
                ->whereMonth('invoice_date', now()->month)
                ->sum('total_amount'),
            'orders_delayed' => (int) DB::table('production_orders')
                ->where('tenant_id', $tenantId)
                ->whereNotNull('planned_end_date')
                ->where('planned_end_date', '<', now())
                ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
                ->count(),
            'stock_value' => (float) DB::table('stock_balances as sb')
                ->join('items as i', 'i.id', '=', 'sb.item_id')
                ->where('sb.tenant_id', $tenantId)
                ->selectRaw('COALESCE(SUM(sb.on_hand * i.default_purchase_price), 0) as value')
                ->value('value'),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }
}
