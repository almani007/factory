<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id ?? 0;

        $inventory = DB::table('stock_balances as sb')
            ->join('items as i', 'i.id', '=', 'sb.item_id')
            ->where('sb.tenant_id', $tenantId)
            ->select([
                'sb.id', 'i.sku', 'i.name as item_name',
                'sb.on_hand', 'sb.reserved', 'sb.on_hand as available',
                DB::raw('COALESCE(SUM(sb.on_hand * i.default_purchase_price), 0) as value'),
            ])
            ->orderBy('i.sku')
            ->get();

        return response()->json(['success' => true, 'data' => $inventory]);
    }
}
