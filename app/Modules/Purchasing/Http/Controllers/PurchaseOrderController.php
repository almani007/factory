<?php

namespace App\Modules\Purchasing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'po_number' => 'required|string|max:100|unique:purchase_orders,po_number',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        return PurchaseOrder::create($validated);
    }

    public function show(PurchaseOrder $po)
    {
        return $po->load(['supplier', 'lines']);
    }

    public function update(Request $request, PurchaseOrder $po)
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'notes' => 'sometimes|nullable|string',
        ]);
        $po->update($validated);
        return $po;
    }

    public function destroy(PurchaseOrder $po)
    {
        $po->delete();
        return response()->noContent();
    }
}