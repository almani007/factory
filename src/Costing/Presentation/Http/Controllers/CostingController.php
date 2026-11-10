<?php

namespace Costing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Costing\Application\Services\CostingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * CostingController
 *
 * Exposes costing engine: actual cost, standard cost, variance.
 */
class CostingController extends Controller
{
    protected CostingService $costingService;

    public function __construct(CostingService $costingService)
    {
        $this->costingService = $costingService;
    }

    /**
     * Calculate actual cost for a completed production order.
     */
    public function calculateCost(Request $request, string $orderId): JsonResponse
    {
        try {
            $cost = $this->costingService->calculateOrderCost($orderId);

            return response()->json([
                'status' => 'success',
                'data' => $cost->load('lines'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'COST_CALCULATION_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Get cost record for an order.
     */
    public function getCost(string $orderId): JsonResponse
    {
        $cost = \Costing\Infrastructure\Persistence\ProductionCost::where(
            'production_order_id', $orderId
        )->latest('costed_at')->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $cost->load('lines'),
        ]);
    }

    /**
     * Calculate variance: actual vs standard with explanation.
     */
    public function variance(string $orderId): JsonResponse
    {
        try {
            $variance = $this->costingService->calculateVariance($orderId);

            return response()->json([
                'status' => 'success',
                'data' => $variance,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'VARIANCE_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Set standard cost for an item.
     */
    public function setStandardCost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'item_id' => 'required|exists:items,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'material_cost' => 'required|numeric|gte:0',
            'labour_cost' => 'required|numeric|gte:0',
            'machine_cost' => 'required|numeric|gte:0',
            'overhead_cost' => 'required|numeric|gte:0',
        ]);

        try {
            $standard = $this->costingService->setStandardCost($validated);

            return response()->json([
                'status' => 'success',
                'data' => $standard,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'STANDARD_COST_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * List standard costs.
     */
    public function standardCosts(Request $request): JsonResponse
    {
        $query = \Costing\Infrastructure\Persistence\StandardCost::where(
            'tenant_id',
            $request->user()->tenant_id ?? $request->input('tenant_id')
        );

        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('effective_from', 'desc')->paginate($request->input('per_page', 15)),
        ]);
    }

    /**
     * List variances.
     */
    public function variances(Request $request): JsonResponse
    {
        $query = \Costing\Infrastructure\Persistence\ProductionCostVariance::where(
            'tenant_id',
            $request->user()->tenant_id ?? $request->input('tenant_id')
        );

        if ($request->has('production_order_id')) {
            $query->where('production_order_id', $request->production_order_id);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('calculated_at', 'desc')
                ->paginate($request->input('per_page', 15)),
        ]);
    }
}