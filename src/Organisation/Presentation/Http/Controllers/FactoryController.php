<?php

namespace Organisation\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tenancy\Infrastructure\Persistence\Factory;

/**
 * FactoryController
 *
 * CRUD for production sites (factories) belonging to a company.
 */
class FactoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $factories = Factory::query()
            ->with('company')
            ->when($request->filled('company_id'), fn ($query) => $query->where('company_id', $request->integer('company_id')))
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $factories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|required|exists:tenants,id',
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:factories,code',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|size:2',
            'timezone' => 'nullable|string|max:64',
            'working_hours' => 'nullable|array',
            'shifts' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] ??= config('tenancy.current_tenant_id');

        $factory = Factory::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $factory,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Factory::with(['company', 'departments', 'workCentres'])->findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $factory = Factory::findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'sometimes|required|exists:companies,id',
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:factories,code,'.$factory->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|size:2',
            'timezone' => 'nullable|string|max:64',
            'working_hours' => 'nullable|array',
            'shifts' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $factory->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $factory,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        Factory::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
