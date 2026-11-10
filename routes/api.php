<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/login', [\Identity\Presentation\Http\Controllers\Auth\LoginController::class, 'store']);
        Route::post('/logout', [\Identity\Presentation\Http\Controllers\Auth\LoginController::class, 'destroy']);
        Route::post('/refresh', [\Identity\Presentation\Http\Controllers\Auth\LoginController::class, 'refresh']);
        Route::get('/user', [\Identity\Presentation\Http\Controllers\Auth\MeController::class, 'show']);
    });

    // Tenants
    Route::apiResource('tenants', \Tenancy\Presentation\Http\Controllers\TenantController::class);

    // Companies
    Route::apiResource('companies', \Tenancy\Presentation\Http\Controllers\CompanyController::class);

    // Factories
    Route::apiResource('factories', \Organisation\Presentation\Http\Controllers\FactoryController::class);

    // Products
    Route::apiResource('products', \Products\Presentation\Http\Controllers\ProductController::class);

    // Inventory
    Route::apiResource('inventory/movements', \Inventory\Presentation\Http\Controllers\StockMovementController::class);

    // Purchasing
    Route::apiResource('suppliers', \Purchasing\Presentation\Http\Controllers\SupplierController::class);
    Route::apiResource('purchase-orders', \Purchasing\Presentation\Http\Controllers\PurchaseOrderController::class);

    // Production
    Route::apiResource('production-orders', \Production\Presentation\Http\Controllers\ProductionOrderController::class);

    // Health
    Route::get('/health', [\Configuration\Presentation\Http\Controllers\HealthController::class, 'check']);
});