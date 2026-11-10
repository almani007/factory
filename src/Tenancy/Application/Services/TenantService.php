<?php

namespace Tenancy\Application\Services;

use Tenancy\Infrastructure\Persistence\Tenant;
use Tenancy\Infrastructure\Persistence\Company;
use Tenancy\Infrastructure\Persistence\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * TenantService
 *
 * Manages multi-tenant onboarding and isolation. Each tenant represents a
 * security and subscription boundary. Company A must never access Company B.
 */
class TenantService
{
    /**
     * Create a new tenant with an initial company and factory.
     */
    public function onboard(array $dto): array
    {
        return DB::transaction(function () use ($dto) {
            // Create tenant
            $tenant = Tenant::create([
                'name' => $dto['tenant_name'],
                'slug' => $dto['tenant_slug'],
                'subdomain' => $dto['subdomain'] ?? null,
                'email' => $dto['email'],
                'phone' => $dto['phone'] ?? null,
                'address' => $dto['address'] ?? null,
                'tax_id' => $dto['tax_id'] ?? null,
                'currency' => $dto['currency'] ?? 'PKR',
                'timezone' => $dto['timezone'] ?? 'Asia/Karachi',
                'locale' => $dto['locale'] ?? 'en',
                'status' => 'active',
                'plan' => $dto['plan'] ?? 'starter',
                'trial_ends_at' => $dto['trial_ends_at'] ?? null,
                'is_active' => true,
            ]);

            // Create initial company
            $company = Company::create([
                'tenant_id' => $tenant->id,
                'name' => $dto['company_name'],
                'slug' => $dto['company_slug'],
                'email' => $dto['company_email'] ?? $dto['email'],
                'phone' => $dto['company_phone'] ?? null,
                'address' => $dto['company_address'] ?? null,
                'city' => $dto['company_city'] ?? null,
                'country' => $dto['company_country'] ?? 'PK',
                'tax_id' => $dto['company_tax_id'] ?? null,
                'currency' => $dto['currency'] ?? 'PKR',
                'tax_jurisdiction' => $dto['tax_jurisdiction'] ?? 'PK',
                'timezone' => $dto['timezone'] ?? 'Asia/Karachi',
                'locale' => $dto['locale'] ?? 'en',
                'is_active' => true,
            ]);

            // Create initial factory
            $factory = Factory::create([
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'name' => $dto['factory_name'],
                'code' => $dto['factory_code'],
                'address' => $dto['factory_address'] ?? null,
                'city' => $dto['factory_city'] ?? null,
                'country' => $dto['factory_country'] ?? 'PK',
                'timezone' => $dto['timezone'] ?? 'Asia/Karachi',
                'working_hours' => $dto['working_hours'] ?? null,
                'shifts' => $dto['shifts'] ?? null,
                'is_active' => true,
            ]);

            Log::info('Tenant onboarded', [
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'factory_id' => $factory->id,
            ]);

            return [
                'tenant' => $tenant,
                'company' => $company,
                'factory' => $factory,
            ];
        });
    }

    /**
     * Suspend a tenant (blocks all operations).
     */
    public function suspend(string $tenantId, int $userId): Tenant
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->status = 'suspended';
        $tenant->save();

        Log::info('Tenant suspended', [
            'tenant_id' => $tenantId,
            'suspended_by' => $userId,
        ]);

        return $tenant;
    }

    /**
     * Reactivate a suspended tenant.
     */
    public function reactivate(string $tenantId, int $userId): Tenant
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->status = 'active';
        $tenant->save();

        Log::info('Tenant reactivated', [
            'tenant_id' => $tenantId,
            'reactivated_by' => $userId,
        ]);

        return $tenant;
    }

    /**
     * Verify tenant isolation - ensure a record belongs to the given tenant.
     */
    public function verifyTenantAccess(string $tenantId, mixed $record): bool
    {
        $recordTenantId = is_object($record) && property_exists($record, 'tenant_id')
            ? $record->tenant_id
            : (is_array($record) && isset($record['tenant_id']) ? $record['tenant_id'] : null);

        return $recordTenantId === $tenantId;
    }
}