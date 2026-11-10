<?php

namespace App\Modules\Organisation\Http\Controllers;

use App\Modules\Organisation\Models\Factory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FactoryController extends Controller
{
    public function index()
    {
        return Factory::with(['departments', 'workCentres', 'warehouses'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
        ]);

        return Factory::create($validated);
    }

    public function show(Factory $factory)
    {
        return $factory->load(['departments', 'workCentres', 'warehouses']);
    }

    public function update(Request $request, Factory $factory)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50',
        ]);
        $factory->update($validated);
        return $factory;
    }

    public function destroy(Factory $factory)
    {
        $factory->delete();
        return response()->noContent();
    }
}