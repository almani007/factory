<?php

namespace App\Modules\Tenancy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        return Tenant::with('companies')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:tenants,slug',
            'subdomain' => 'required|string|max:100|unique:tenants,subdomain',
            'email' => 'required|email|unique:tenants,email',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string',
            'plan' => 'required|string',
        ]);

        return Tenant::create($validated);
    }

    public function show(Tenant $tenant)
    {
        return $tenant->load('companies');
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'plan' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ]);
        $tenant->update($validated);
        return $tenant;
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return response()->noContent();
    }
}