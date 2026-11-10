<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::prefix('v1')->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/login', [\App\Modules\Identity\Http\Controllers\Auth\LoginController::class, 'store']);
        Route::post('/logout', [\App\Modules\Identity\Http\Controllers\Auth\LoginController::class, 'destroy']);
        Route::post('/refresh', [\App\Modules\Identity\Http\Controllers\Auth\LoginController::class, 'refresh']);
        Route::get('/user', [\App\Modules\Identity\Http\Controllers\Auth\MeController::class, 'show']);
    });

    // Tenants
    Route::apiResource('tenants', \App\Modules\Tenancy\Http\Controllers\TenantController::class);

    // Companies
    Route::apiResource('companies', \App\Modules\Tenancy\Http\Controllers\CompanyController::class);

    // Factories
    Route::apiResource('factories', \App\Modules\Organisation\Http\Controllers\FactoryController::class);

    // Products
    Route::apiResource('products', \App\Modules\Products\Http\Controllers\ProductController::class);

    // Inventory
    Route::apiResource('inventory/movements', \App\Modules\Inventory\Http\Controllers\StockMovementController::class);

    // Purchasing
    Route::apiResource('suppliers', \App\Modules\Purchasing\Http\Controllers\SupplierController::class);
    Route::apiResource('purchase-orders', \App\Modules\Purchasing\Http\Controllers\PurchaseOrderController::class);

    // Production
    Route::apiResource('production-orders', \App\Modules\Production\Http\Controllers\ProductionOrderController::class);

    // Health
    Route::get('/health', [\App\Modules\Core\Http\Controllers\HealthController::class, 'check']);
});