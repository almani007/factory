<?php

namespace Manufacturing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Manufacturing\Application\Services\BOMService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * BOMController
 *
 * Exposes BOM and routing configuration (configurable manufacturing operations).
 */
class BOMController extends Controller
{
    protected BOMService $bomService;

    public function __construct(BOMService $bomService)
    {
        $this->bomService = $bomService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = \Manufacturing\Infrastructure\Persistence\BOMHeader::with('versions')
            ->where('tenant_id', $request->user()->tenant_id ?? request('tenant_id'));

        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate($request->input('per_page', 15)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'factory_id' => 'nullable|exists:factories,id',
            'is_default' => 'nullable|boolean',
        ]);

        $bom = $this->bomService->createHeader(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
        ));

        return response()->json([
            'status' => 'success',
            'data' => $bom,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $bom = \Manufacturing\Infrastructure\Persistence\BOMHeader::with('versions.components.item')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $bom,
        ]);
    }

    /**
     * Create a BOM version (controls effective dates).
     */
    public function createVersion(Request $request, string $bomHeaderId): JsonResponse
    {
        $validated = $request->validate([
            'version' => 'required|string|max:50',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'is_active' => 'nullable|boolean',
            'standard_material_cost' => 'nullable|numeric|gte:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $version = $this->bomService->createVersion(array_merge(
                $validated,
                ['bom_header_id' => $bomHeaderId,
                 'tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
            ));

            return response()->json([
                'status' => 'success',
                'data' => $version,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'VERSION_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Get components for a BOM version.
     */
    public function components(string $bomVersionId): JsonResponse
    {
        $components = $this->bomService->getComponents($bomVersionId);

        return response()->json([
            'status' => 'success',
            'data' => $components,
        ]);
    }

    /**
     * Add components to a BOM version.
     */
    public function addComponents(Request $request, string $bomVersionId): JsonResponse
    {
        $validated = $request->validate([
            'components' => 'required|array|min:1',
            'components.*.item_id' => 'required|exists:items,id',
            'components.*.quantity' => 'required|numeric|gt:0',
            'components.*.unit_id' => 'nullable|exists:units,id',
            'components.*.expected_wastage_percentage' => 'nullable|numeric|gte:0',
            'components.*.is_alternative' => 'nullable|boolean',
            'components.*.notes' => 'nullable|string',
        ]);

        $components = $this->bomService->addComponents($bomVersionId, array_map(
            fn($c) => array_merge($c, ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]),
            $validated['components']
        ));

        return response()->json([
            'status' => 'success',
            'data' => $components,
        ], 201);
    }

    /**
     * Get active BOM version for an item.
     */
    public function activeVersion(Request $request, string $bomHeaderId): JsonResponse
    {
        $version = $this->bomService->getActiveVersion(
            $bomHeaderId,
            $request->input('effective_date')
        );

        if (!$version) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'NO_ACTIVE_VERSION', 'message' => 'No active BOM version found.'],
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $version,
        ]);
    }

    /**
     * List routings.
     */
    public function routings(Request $request): JsonResponse
    {
        $query = \Manufacturing\Infrastructure\Persistence\Routing::with('versions.operations')
            ->where('tenant_id', $request->user()->tenant_id ?? request('tenant_id'));

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate($request->input('per_page', 15)),
        ]);
    }

    /**
     * Create a routing version with operations.
     */
    public function createRoutingVersion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'routing_id' => 'required|exists:routings,id',
            'version' => 'required|string|max:50',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'operations' => 'nullable|array',
            'operations.*.sequence' => 'nullable|integer',
            'operations.*.operation_name' => 'nullable|string',
            'operations.*.work_centre_id' => 'nullable|exists:work_centres,id',
            'operations.*.machine_id' => 'nullable|exists:machines,id',
            'operations.*.setup_time_minutes' => 'nullable|integer|gte:0',
            'operations.*.run_time_per_unit_minutes' => 'nullable|numeric|gte:0',
        ]);

        try {
            $version = $this->bomService->createRoutingVersion(array_merge(
                $validated,
                ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
            ));

            return response()->json([
                'status' => 'success',
                'data' => $version,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'ROUTING_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }
}