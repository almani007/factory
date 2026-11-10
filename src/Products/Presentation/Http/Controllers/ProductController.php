<?php

namespace Products\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Products\Infrastructure\Persistence\Item;

/**
 * ProductController
 *
 * Product master data for the current tenant.
 */
class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Item::query()
            ->with(['category', 'unit'])
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('sku', 'ilike', "%{$search}%")
                        ->orWhere('name', 'ilike', "%{$search}%")
                        ->orWhere('barcode', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|required|exists:tenants,id',
            'factory_id' => 'nullable|exists:factories,id',
            'category_id' => 'nullable|exists:item_categories,id',
            'sku' => 'required|string|max:100|unique:items,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:100',
            'unit_id' => 'nullable|exists:units,id',
            'barcode' => 'nullable|string|max:100',
            'image' => 'nullable|string|max:2048',
            'specifications' => 'nullable|array',
            'min_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'preferred_supplier_id' => 'nullable|integer',
            'default_purchase_price' => 'nullable|numeric|min:0',
            'standard_production_cost' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'track_batch' => 'nullable|boolean',
            'track_serial' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] ??= config('tenancy.current_tenant_id');

        $product = Item::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $product->load(['category', 'unit']),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Item::with(['category', 'unit', 'variants'])->findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $product = Item::findOrFail($id);

        $validated = $request->validate([
            'factory_id' => 'nullable|exists:factories,id',
            'category_id' => 'nullable|exists:item_categories,id',
            'sku' => 'sometimes|required|string|max:100|unique:items,sku,'.$product->id,
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:100',
            'unit_id' => 'nullable|exists:units,id',
            'barcode' => 'nullable|string|max:100',
            'image' => 'nullable|string|max:2048',
            'specifications' => 'nullable|array',
            'min_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'preferred_supplier_id' => 'nullable|integer',
            'default_purchase_price' => 'nullable|numeric|min:0',
            'standard_production_cost' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'track_batch' => 'nullable|boolean',
            'track_serial' => 'nullable|boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $product->load(['category', 'unit']),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        Item::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
