<?php

namespace App\Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Item;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['category', 'unit']);

        if ($request->has('search')) {
            $query->where('sku', 'like', '%'.$request->search.'%')
                ->orWhere('name', 'like', '%'.$request->search.'%');
        }

        return $query->paginate($request->input('per_page', 15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:item_categories,id',
            'unit_id' => 'required|exists:units,id',
            'description' => 'nullable|string',
            'barcode' => 'nullable|string',
        ]);

        return Item::create($validated);
    }

    public function show(Item $item)
    {
        return $item->load(['category', 'unit', 'variants']);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'sku' => 'sometimes|string|max:100',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'barcode' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);
        $item->update($validated);
        return $item;
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return response()->noContent();
    }
}