<?php

namespace Production\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Production\Application\Services\ProductionOrderService;
use Manufacturing\Application\Services\BOMService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Shared\Domain\Exceptions\InsufficientStockException;

/**
 * ProductionOrderController
 *
 * Exposes production order lifecycle via API:
 *   Draft → Pending Approval → Approved → Material Reserved → In Production
 *   → Quality Inspection → Completed → Closed
 */
class ProductionOrderController extends Controller
{
    protected ProductionOrderService $productionService;
    protected BOMService $bomService;

    public function __construct(ProductionOrderService $productionService, BOMService $bomService)
    {
        $this->productionService = $productionService;
        $this->bomService = $bomService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = \Production\Infrastructure\Persistence\ProductionOrder::with(['bomVersion', 'workOrders'])
            ->where('tenant_id', $request->user()->tenant_id ?? request('tenant_id'));

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:100|unique:production_orders,order_number',
            'bom_header_id' => 'required|exists:bom_headers,id',
            'bom_version_id' => 'required|exists:bom_versions,id',
            'routing_version_id' => 'nullable|exists:routing_versions,id',
            'planned_quantity' => 'required|numeric|gt:0',
            'planned_start_date' => 'required|date',
            'planned_end_date' => 'required|date|after:planned_start_date',
            'factory_id' => 'required|exists:factories,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'priority' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $order = $this->productionService->createOrder(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
        ));

        return response()->json([
            'status' => 'success',
            'data' => $order->load('bomVersion'),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $order = \Production\Infrastructure\Persistence\ProductionOrder::with([
            'bomVersion', 'workOrders', 'materialIssues.lines', 'productionEntries.outputs',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $order,
        ]);
    }

    /**
     * Submit for approval (Draft → Pending Approval).
     */
    public function submit(Request $request, string $id): JsonResponse
    {
        $order = \Production\Infrastructure\Persistence\ProductionOrder::findOrFail($id);

        try {
            $order = $this->productionService->submitForApproval(
                $order,
                $request->user()->id ?? $request->input('user_id')
            );

            return response()->json([
                'status' => 'success',
                'data' => $order,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'INVALID_TRANSITION', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Approve order (Pending Approval → Approved). Locks in BOM version (BR-006).
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $order = \Production\Infrastructure\Persistence\ProductionOrder::findOrFail($id);

        try {
            $order = $this->productionService->approveOrder(
                $order,
                $request->user()->id ?? $request->input('user_id')
            );

            return response()->json([
                'status' => 'success',
                'data' => $order->load('bomVersion'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'APPROVAL_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Reserve materials (Approved → Material Reserved). Validates availability (BR-005).
     */
    public function reserveMaterials(Request $request, string $id): JsonResponse
    {
        $order = \Production\Infrastructure\Persistence\ProductionOrder::findOrFail($id);

        try {
            $order = $this->productionService->reserveMaterials(
                $order,
                $request->user()->id ?? $request->input('user_id')
            );

            return response()->json([
                'status' => 'success',
                'data' => $order,
            ]);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'INSUFFICIENT_STOCK', 'message' => $e->getMessage()],
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'RESERVATION_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Start production (Material Reserved → In Production).
     */
    public function start(Request $request, string $id): JsonResponse
    {
        $order = \Production\Infrastructure\Persistence\ProductionOrder::findOrFail($id);

        try {
            $order = $this->productionService->startProduction(
                $order,
                $request->user()->id ?? $request->input('user_id')
            );

            return response()->json([
                'status' => 'success',
                'data' => $order,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'INVALID_TRANSITION', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Record production output (good, rejected, rework, scrap).
     */
    public function recordOutput(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'work_order_id' => 'nullable|exists:work_orders,id',
            'entry_date' => 'nullable|date',
            'good_quantity' => 'nullable|numeric|gte:0',
            'rejected_quantity' => 'nullable|numeric|gte:0',
            'rework_quantity' => 'nullable|numeric|gte:0',
            'scrap_quantity' => 'nullable|numeric|gte:0',
            'operator_id' => 'nullable|exists:users,id',
            'machine_id' => 'nullable|exists:machines,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $entry = $this->productionService->recordOutput(array_merge(
                $validated,
                ['production_order_id' => $id]
            ));

            return response()->json([
                'status' => 'success',
                'data' => $entry,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'OUTPUT_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Complete production (Quality Inspection → Completed).
     */
    public function complete(Request $request, string $id): JsonResponse
    {
        $order = \Production\Infrastructure\Persistence\ProductionOrder::findOrFail($id);

        try {
            $order = $this->productionService->completeProduction(
                $order,
                $request->user()->id ?? $request->input('user_id')
            );

            return response()->json([
                'status' => 'success',
                'data' => $order,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'INVALID_TRANSITION', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Close production order (Completed → Closed).
     */
    public function close(Request $request, string $id): JsonResponse
    {
        $order = \Production\Infrastructure\Persistence\ProductionOrder::findOrFail($id);

        try {
            $order = $this->productionService->closeOrder(
                $order,
                $request->user()->id ?? $request->input('user_id')
            );

            return response()->json([
                'status' => 'success',
                'data' => $order,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'INVALID_TRANSITION', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Cancel production order (with audit).
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $order = \Production\Infrastructure\Persistence\ProductionOrder::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        try {
            $order = $this->productionService->cancelOrder(
                $order,
                $request->user()->id ?? $request->input('user_id'),
                $validated['reason']
            );

            return response()->json([
                'status' => 'success',
                'data' => $order,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'CANCEL_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Calculate material requirements from BOM for a quantity.
     */
    public function calculateMaterials(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bom_version_id' => 'required|exists:bom_versions,id',
            'quantity' => 'required|numeric|gt:0',
        ]);

        $requirements = $this->productionService->calculateMaterialRequirements(
            $validated['bom_version_id'],
            (float) $validated['quantity']
        );

        return response()->json([
            'status' => 'success',
            'data' => $requirements,
        ]);
    }
}