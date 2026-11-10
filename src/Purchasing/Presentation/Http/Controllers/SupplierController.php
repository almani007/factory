<?php

namespace Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Purchasing\Application\Services\PurchasingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * SupplierController - supplier management.
 */
class SupplierController extends Controller
{
    protected PurchasingService $purchasingService;

    public function __construct(PurchasingService $purchasingService)
    {
        $this->purchasingService = $purchasingService;
    }

    public function index(Request $request): JsonResponse
    {
        $suppliers = $this->purchasingService->listSuppliers([
            'tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id'),
            'search' => $request->input('search'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $suppliers,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:suppliers,code',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:100',
            'category' => 'nullable|string',
        ]);

        $supplier = \Purchasing\Infrastructure\Persistence\Supplier::create(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
        ));

        return response()->json([
            'status' => 'success',
            'data' => $supplier,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $supplier = \Purchasing\Infrastructure\Persistence\Supplier::findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $supplier]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $supplier = \Purchasing\Infrastructure\Persistence\Supplier::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|nullable|email',
            'phone' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);
        $supplier->update($validated);
        return response()->json(['status' => 'success', 'data' => $supplier]);
    }
}