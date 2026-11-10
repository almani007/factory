<?php

namespace Tenancy\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tenancy\Infrastructure\Persistence\Company;

/**
 * CompanyController
 *
 * CRUD for companies belonging to the current tenant.
 */
class CompanyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $companies = Company::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('slug', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $companies,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:companies,slug',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|size:2',
            'tax_id' => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
            'tax_jurisdiction' => 'nullable|string|size:2',
            'timezone' => 'nullable|string|max:64',
            'locale' => 'nullable|string|max:10',
            'branding' => 'nullable|array',
            'settings' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] ??= config('tenancy.current_tenant_id');

        $company = Company::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $company,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Company::with(['factories'])->findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:100|unique:companies,slug,'.$company->id,
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|size:2',
            'tax_id' => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
            'tax_jurisdiction' => 'nullable|string|size:2',
            'timezone' => 'nullable|string|max:64',
            'locale' => 'nullable|string|max:10',
            'branding' => 'nullable|array',
            'settings' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $company->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $company,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        Company::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
