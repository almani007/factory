<?php

namespace Dispatch\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Dispatch\Application\Services\DispatchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * DispatchController - dispatch and delivery management.
 */
class DispatchController extends Controller
{
    protected DispatchService $dispatchService;

    public function __construct(DispatchService $dispatchService)
    {
        $this->dispatchService = $dispatchService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = \Dispatch\Infrastructure\Persistence\Dispatch::with(['lines.item', 'salesOrder'])
            ->where('tenant_id', $request->user()->tenant_id ?? $request->input('tenant_id'));

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 15)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'factory_id' => 'required|exists:factories,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'dispatch_number' => 'required|string|max:100|unique:dispatches,dispatch_number',
            'dispatch_date' => 'required|date',
            'vehicle_details' => 'nullable|array',
            'driver_name' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $dispatch = $this->dispatchService->createDispatch(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
        ));

        return response()->json([
            'status' => 'success',
            'data' => $dispatch,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $dispatch = \Dispatch\Infrastructure\Persistence\Dispatch::with('lines.item')->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $dispatch]);
    }

    /**
     * Add lines to a dispatch.
     */
    public function addLines(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.unit_id' => 'nullable|exists:units,id',
        ]);

        $lines = $this->dispatchService->addDispatchLines($id, $validated['lines']);

        return response()->json([
            'status' => 'success',
            'data' => $lines,
        ], 201);
    }

    /**
     * Approve and execute dispatch (creates stock movement).
     */
    public function execute(Request $request, string $id): JsonResponse
    {
        $dispatch = \Dispatch\Infrastructure\Persistence\Dispatch::findOrFail($id);

        try {
            $dispatch = $this->dispatchService->executeDispatch(
                $dispatch,
                $request->user()->id ?? $request->input('user_id')
            );

            return response()->json([
                'status' => 'success',
                'data' => $dispatch,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'DISPATCH_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Create a delivery note.
     */
    public function createDeliveryNote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dispatch_id' => 'required|exists:dispatches,id',
            'note_number' => 'required|string|max:100|unique:delivery_notes,note_number',
            'note_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $note = $this->dispatchService->createDeliveryNote($validated);

        return response()->json([
            'status' => 'success',
            'data' => $note,
        ], 201);
    }

    /**
     * Mark dispatch as delivered.
     */
    public function markDelivered(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'proof_of_delivery' => 'nullable|string',
        ]);

        $dispatch = \Dispatch\Infrastructure\Persistence\Dispatch::findOrFail($id);

        try {
            $dispatch = $this->dispatchService->markDelivered(
                $dispatch,
                $request->user()->id ?? $request->input('user_id'),
                $validated['proof_of_delivery'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'data' => $dispatch,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'DELIVERY_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }
}