<?php

namespace Quality\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Quality\Application\Services\QualityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * QualityController
 *
 * Exposes quality inspection and release gate.
 * Finished goods must not become available for sale until quality checks pass (BR-007).
 */
class QualityController extends Controller
{
    protected QualityService $qualityService;

    public function __construct(QualityService $qualityService)
    {
        $this->qualityService = $qualityService;
    }

    /**
     * List inspections.
     */
    public function index(Request $request): JsonResponse
    {
        $query = \Quality\Infrastructure\Persistence\QualityInspection::with('results')
            ->where('tenant_id', $request->user()->tenant_id ?? request('tenant_id'));

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('inspection_type')) {
            $query->where('inspection_type', $request->inspection_type);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15)),
        ]);
    }

    /**
     * Create an inspection from a template.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'factory_id' => 'required|exists:factories,id',
            'inspection_template_id' => 'required|exists:inspection_templates,id',
            'inspection_type' => 'required|string|in:incoming,in_process,final',
            'reference_type' => 'required|string',
            'reference_id' => 'required|integer',
            'inspected_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $inspection = $this->qualityService->createInspection(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
        ));

        return response()->json([
            'status' => 'success',
            'data' => $inspection,
        ], 201);
    }

    /**
     * Get an inspection with results.
     */
    public function show(string $id): JsonResponse
    {
        $inspection = \Quality\Infrastructure\Persistence\QualityInspection::with('results')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $inspection,
        ]);
    }

    /**
     * Record inspection results (pass/fail per checklist item).
     */
    public function recordResults(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'results' => 'required|array|min:1',
            'results.*.template_item_id' => 'required|exists:inspection_template_items,id',
            'results.*.result' => 'required|string|in:pass,fail',
            'results.*.measured_value' => 'nullable|numeric',
            'results.*.notes' => 'nullable|string',
        ]);

        try {
            $inspection = $this->qualityService->recordResults(
                $id,
                $validated['results'],
                $request->user()->id ?? $request->input('user_id')
            );

            return response()->json([
                'status' => 'success',
                'data' => $inspection->load('results'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'RESULTS_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Approve inspection (quality release gate).
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'comments' => 'nullable|string',
        ]);

        try {
            $inspection = $this->qualityService->approveInspection(
                $id,
                $request->user()->id ?? $request->input('user_id'),
                $validated['comments'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'data' => $inspection,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'APPROVAL_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Reject inspection.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        try {
            $inspection = $this->qualityService->rejectInspection(
                $id,
                $request->user()->id ?? $request->input('user_id'),
                $validated['reason']
            );

            return response()->json([
                'status' => 'success',
                'data' => $inspection,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'REJECTION_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Check if finished goods can be released (quality gate).
     */
    public function canRelease(Request $request, string $productionOrderId): JsonResponse
    {
        $canRelease = $this->qualityService->canReleaseFinishedGoods($productionOrderId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'can_release' => $canRelease,
                'production_order_id' => $productionOrderId,
            ],
        ]);
    }

    /**
     * Record a defect.
     */
    public function recordDefect(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'factory_id' => 'required|exists:factories,id',
            'quality_inspection_id' => 'nullable|exists:quality_inspections,id',
            'defect_category' => 'required|string',
            'defect_code' => 'required|string',
            'description' => 'required|string',
            'quantity' => 'required|numeric|gt:0',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        $defect = \Quality\Infrastructure\Persistence\QualityDefect::create(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
        ));

        return response()->json([
            'status' => 'success',
            'data' => $defect,
        ], 201);
    }
}