<?php

namespace App\Modules\Tenancy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenancy\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        return Company::with('factories')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
            'tax_jurisdiction' => 'required|string|size:2',
            'timezone' => 'required|string',
        ]);

        return Company::create($validated);
    }

    public function show(Company $company)
    {
        return $company->load('factories');
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'currency' => 'sometimes|string|size:3',
            'is_active' => 'sometimes|boolean',
        ]);
        $company->update($validated);
        return $company;
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return response()->noContent();
    }
}