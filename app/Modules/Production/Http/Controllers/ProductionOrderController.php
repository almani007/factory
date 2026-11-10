<?php

namespace App\Modules\Production\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Models\ProductionOrder;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionOrder::with(['bomVersion', 'workOrders']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:100|unique:production_orders,order_number',
            'bom_header_id' => 'required|exists:bom_headers,id',
            'bom_version_id' => 'required|exists:bom_versions,id',
            'planned_quantity' => 'required|numeric|gt:0',
            'planned_start_date' => 'required|date',
            'planned_end_date' => 'required|date|after:planned_start_date',
            'priority' => 'sometimes|string|max:50',
            'notes' => 'nullable|string',
        ]);

        return ProductionOrder::create([
            ...$validated,
            'status' => ProductionOrder::STATUS_DRAFT,
        ]);
    }

    public function show(ProductionOrder $order)
    {
        return $order->load(['bomVersion', 'workOrders', 'materialIssues', 'productionEntries']);
    }

    public function update(Request $request, ProductionOrder $order)
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'planned_quantity' => 'sometimes|numeric|gt:0',
            'notes' => 'sometimes|nullable|string',
        ]);
        $order->update($validated);
        return $order;
    }

    public function destroy(ProductionOrder $order)
    {
        $order->delete();
        return response()->noContent();
    }
}