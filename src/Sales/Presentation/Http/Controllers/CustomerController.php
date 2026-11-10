<?php

namespace Sales\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Sales\Application\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * CustomerController - customer management.
 */
class CustomerController extends Controller
{
    protected SalesService $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = \Sales\Infrastructure\Persistence\Customer::where(
            'tenant_id',
            $request->user()->tenant_id ?? $request->input('tenant_id')
        )->where('is_active', true);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate($request->input('per_page', 15)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:customers,code',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string|size:2',
            'tax_id' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|gte:0',
            'payment_terms' => 'nullable|string|max:100',
        ]);

        $customer = $this->salesService->createCustomer(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
        ));

        return response()->json([
            'status' => 'success',
            'data' => $customer,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $customer = \Sales\Infrastructure\Persistence\Customer::findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $customer]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $customer = \Sales\Infrastructure\Persistence\Customer::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|nullable|email',
            'phone' => 'sometimes|nullable|string',
            'credit_limit' => 'sometimes|nullable|numeric|gte:0',
            'is_active' => 'sometimes|boolean',
        ]);
        $customer->update($validated);
        return response()->json(['status' => 'success', 'data' => $customer]);
    }
}