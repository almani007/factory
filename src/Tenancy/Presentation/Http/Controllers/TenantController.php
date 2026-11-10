<?php

namespace Tenancy\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Tenancy\Application\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * TenantController
 *
 * Exposes tenant onboarding and management.
 * Each tenant is a security and subscription boundary.
 */
class TenantController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = \Tenancy\Infrastructure\Persistence\Tenant::with(['companies.factories'])
            ->where('id', $request->user()->tenant_id ?? request('tenant_id'));

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $tenant = \Tenancy\Infrastructure\Persistence\Tenant::with(['companies.factories'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $tenant,
        ]);
    }

    /**
     * Onboard a new tenant (company + factory).
     */
    public function onboard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_name' => 'required|string|max:255',
            'tenant_slug' => 'required|string|max:100|unique:tenants,slug',
            'subdomain' => 'nullable|string|max:100|unique:tenants,subdomain',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'currency' => 'nullable|string|size:3',
            'timezone' => 'nullable|string',
            'plan' => 'nullable|string',
            'trial_ends_at' => 'nullable|date',

            'company_name' => 'required|string|max:255',
            'company_slug' => 'required|string|max:100|unique:companies,slug',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable|string',
            'company_address' => 'nullable|string',
            'company_city' => 'nullable|string',
            'company_country' => 'nullable|string|size:2',
            'company_tax_id' => 'nullable|string',
            'tax_jurisdiction' => 'nullable|string|size:2',

            'factory_name' => 'required|string|max:255',
            'factory_code' => 'required|string|max:50|unique:factories,code',
            'factory_address' => 'nullable|string',
            'factory_city' => 'nullable|string',
            'factory_country' => 'nullable|string|size:2',
            'working_hours' => 'nullable|array',
            'shifts' => 'nullable|array',
        ]);

        try {
            $result = $this->tenantService->onboard($validated);

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'ONBOARDING_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Suspend a tenant.
     */
    public function suspend(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $tenant = $this->tenantService->suspend($id, (int) $validated['user_id']);

            return response()->json([
                'status' => 'success',
                'data' => $tenant,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'SUSPEND_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Reactivate a tenant.
     */
    public function reactivate(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $tenant = $this->tenantService->reactivate($id, (int) $validated['user_id']);

            return response()->json([
                'status' => 'success',
                'data' => $tenant,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'REACTIVATE_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }
}