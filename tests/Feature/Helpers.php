<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

function seedTestDatabase(): void
{
    $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
        'name' => 'Test Tenant',
        'slug' => 'test-tenant',
        'subdomain' => 'test-tenant',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
        'address' => 'Test Address',
        'currency' => 'USD',
        'timezone' => 'UTC',
        'locale' => 'en',
        'status' => 'active',
        'plan' => 'enterprise',
        'trial_ends_at' => now()->addYear(),
        'subscription_ends_at' => now()->addYear(),
        'settings' => '{}',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $companyId = \Illuminate\Support\Facades\DB::table('companies')->insertGetId([
        'tenant_id' => $tenantId,
        'name' => 'Test Company',
        'slug' => 'test-company',
        'email' => 'finance@test.com',
        'phone' => '+1234567890',
        'address' => 'Test Address',
        'city' => 'Test City',
        'country' => 'US',
        'tax_id' => 'NTN-1234567',
        'currency' => 'USD',
        'tax_jurisdiction' => 'US',
        'timezone' => 'UTC',
        'locale' => 'en',
        'settings' => '{}',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [$tenantId, $companyId];
}
