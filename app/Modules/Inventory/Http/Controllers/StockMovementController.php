<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['warehouse', 'item', 'lines.item']);

        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->has('from_date')) {
            $query->where('transaction_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('transaction_date', '<=', $request->to_date);
        }

        return $query->orderBy('transaction_date', 'desc')->paginate($request->input('per_page', 15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'movement_type' => 'required|string',
            'reference' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|gt:0',
        ]);

        $movement = StockMovement::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'posted_at' => now(),
            'posted_by' => $request->user()->id,
        ]);

        foreach ($validated['lines'] as $line) {
            $movement->lines()->create($line);
        }

        return $movement->load('lines.item');
    }

    public function show(StockMovement $movement)
    {
        return $movement->load(['warehouse', 'item', 'lines.item', 'user']);
    }
}