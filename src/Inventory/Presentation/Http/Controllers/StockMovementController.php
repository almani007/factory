<?php

namespace Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Inventory\Application\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Shared\Domain\Exceptions\InsufficientStockException;

/**
 * StockMovementController
 *
 * Exposes inventory ledger operations via API.
 * Every stock change creates a traceable stock ledger entry.
 */
class StockMovementController extends Controller
{
    protected StockMovementService $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * List stock movements with filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = \Inventory\Infrastructure\Persistence\StockMovement::with(['lines.item', 'warehouse'])
            ->where('tenant_id', $request->user()->tenant_id ?? request('tenant_id'));

        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->has('from_date')) {
            $query->where('transaction_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('transaction_date', '<=', $request->to_date);
        }
        if ($request->has('search')) {
            $query->where('reference', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('transaction_date', 'desc')
                ->paginate($request->input('per_page', 15)),
        ]);
    }

    /**
     * Post a stock movement (atomic).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'movement_type' => 'required|string',
            'reference' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'posting_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.unit_cost' => 'nullable|numeric',
        ]);

        try {
            $movement = $this->stockService->postMovement(array_merge(
                $validated,
                ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
            ));

            return response()->json([
                'status' => 'success',
                'data' => $movement->load('lines.item'),
            ], 201);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'INSUFFICIENT_STOCK',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Get a single movement.
     */
    public function show(string $id): JsonResponse
    {
        $movement = \Inventory\Infrastructure\Persistence\StockMovement::with('lines.item')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $movement,
        ]);
    }

    /**
     * Reverse a posted movement (compensating transaction).
     */
    public function reverse(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $reversal = $this->stockService->reverseMovement(
                (int) $id,
                $validated['reason'],
                (int) $validated['user_id']
            );

            return response()->json([
                'status' => 'success',
                'data' => $reversal->load('lines.item'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'REVERSAL_FAILED',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Get available quantity for an item.
     */
    public function available(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'item_id' => 'required|exists:items,id',
            'factory_id' => 'nullable|exists:factories,id',
            'item_variant_id' => 'nullable|exists:item_variants,id',
            'batch_id' => 'nullable|exists:stock_batches,id',
        ]);

        $available = $this->stockService->getAvailableQuantity(
            $request->user()->tenant_id ?? $request->input('tenant_id'),
            $validated['factory_id'] ?? null,
            $validated['warehouse_id'],
            $validated['item_id'],
            $validated['item_variant_id'] ?? null,
            $validated['batch_id'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'available_quantity' => $available,
                'warehouse_id' => $validated['warehouse_id'],
                'item_id' => $validated['item_id'],
            ],
        ]);
    }
}