<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ============ Authentication ============
    Route::prefix('auth')->group(function () {
        Route::post('/login', [\Identity\Presentation\Http\Controllers\LoginController::class, 'store']);
        Route::post('/logout', [\Identity\Presentation\Http\Controllers\LoginController::class, 'destroy']);
        Route::post('/refresh', [\Identity\Presentation\Http\Controllers\LoginController::class, 'refresh']);
        Route::get('/user', [\Identity\Presentation\Http\Controllers\MeController::class, 'show']);
    });

    // ============ Tenancy ============
    Route::apiResource('tenants', \Tenancy\Presentation\Http\Controllers\TenantController::class);
    Route::post('/tenants/onboard', [\Tenancy\Presentation\Http\Controllers\TenantController::class, 'onboard']);
    Route::post('/tenants/{id}/suspend', [\Tenancy\Presentation\Http\Controllers\TenantController::class, 'suspend']);
    Route::post('/tenants/{id}/reactivate', [\Tenancy\Presentation\Http\Controllers\TenantController::class, 'reactivate']);

    // ============ Companies ============
    Route::apiResource('companies', \Tenancy\Presentation\Http\Controllers\CompanyController::class);

    // ============ Factories & Organisation ============
    Route::apiResource('factories', \Organisation\Presentation\Http\Controllers\FactoryController::class);

    // ============ Products ============
    Route::apiResource('products', \Products\Presentation\Http\Controllers\ProductController::class);

    // ============ Inventory ============
    Route::apiResource('inventory/movements', \Inventory\Presentation\Http\Controllers\StockMovementController::class);
    Route::post('/inventory/movements/{id}/reverse', [\Inventory\Presentation\Http\Controllers\StockMovementController::class, 'reverse']);
    Route::get('/inventory/available', [\Inventory\Presentation\Http\Controllers\StockMovementController::class, 'available']);

    // ============ Purchasing ============
    Route::apiResource('suppliers', \Purchasing\Presentation\Http\Controllers\SupplierController::class);
    Route::apiResource('purchase-orders', \Purchasing\Presentation\Http\Controllers\PurchaseOrderController::class);
    Route::post('/purchase-orders/{id}/lines', [\Purchasing\Presentation\Http\Controllers\PurchaseOrderController::class, 'addLines']);
    Route::post('/purchase-orders/{id}/receive', [\Purchasing\Presentation\Http\Controllers\PurchaseOrderController::class, 'receive']);
    Route::post('/purchase-orders/invoice', [\Purchasing\Presentation\Http\Controllers\PurchaseOrderController::class, 'createInvoice']);

    // ============ Production ============
    Route::apiResource('production-orders', \Production\Presentation\Http\Controllers\ProductionOrderController::class);
    Route::post('/production-orders/{id}/submit', [\Production\Presentation\Http\Controllers\ProductionOrderController::class, 'submit']);
    Route::post('/production-orders/{id}/approve', [\Production\Presentation\Http\Controllers\ProductionOrderController::class, 'approve']);
    Route::post('/production-orders/{id}/reserve-materials', [\Production\Presentation\Http\Controllers\ProductionOrderController::class, 'reserveMaterials']);
    Route::post('/production-orders/{id}/start', [\Production\Presentation\Http\Controllers\ProductionOrderController::class, 'start']);
    Route::post('/production-orders/{id}/record-output', [\Production\Presentation\Http\Controllers\ProductionOrderController::class, 'recordOutput']);
    Route::post('/production-orders/{id}/complete', [\Production\Presentation\Http\Controllers\ProductionOrderController::class, 'complete']);
    Route::post('/production-orders/{id}/close', [\Production\Presentation\Http\Controllers\ProductionOrderController::class, 'close']);
    Route::post('/production-orders/{id}/cancel', [\Production\Presentation\Http\Controllers\ProductionOrderController::class, 'cancel']);
    Route::post('/production/calculate-materials', [\Production\Presentation\Http\Controllers\ProductionOrderController::class, 'calculateMaterials']);

    // ============ BOM & Manufacturing ============
    Route::apiResource('boms', \Manufacturing\Presentation\Http\Controllers\BOMController::class);
    Route::post('/boms/{bomHeaderId}/versions', [\Manufacturing\Presentation\Http\Controllers\BOMController::class, 'createVersion']);
    Route::get('/boms/{bomHeaderId}/active-version', [\Manufacturing\Presentation\Http\Controllers\BOMController::class, 'activeVersion']);
    Route::get('/bom-versions/{bomVersionId}/components', [\Manufacturing\Presentation\Http\Controllers\BOMController::class, 'components']);
    Route::post('/bom-versions/{bomVersionId}/components', [\Manufacturing\Presentation\Http\Controllers\BOMController::class, 'addComponents']);
    Route::get('/routings', [\Manufacturing\Presentation\Http\Controllers\BOMController::class, 'routings']);
    Route::post('/routing-versions', [\Manufacturing\Presentation\Http\Controllers\BOMController::class, 'createRoutingVersion']);

    // ============ Quality ============
    Route::apiResource('quality/inspections', \Quality\Presentation\Http\Controllers\QualityController::class);
    Route::post('/quality/inspections/{id}/results', [\Quality\Presentation\Http\Controllers\QualityController::class, 'recordResults']);
    Route::post('/quality/inspections/{id}/approve', [\Quality\Presentation\Http\Controllers\QualityController::class, 'approve']);
    Route::post('/quality/inspections/{id}/reject', [\Quality\Presentation\Http\Controllers\QualityController::class, 'reject']);
    Route::get('/quality/inspections/{productionOrderId}/can-release', [\Quality\Presentation\Http\Controllers\QualityController::class, 'canRelease']);
    Route::post('/quality/defects', [\Quality\Presentation\Http\Controllers\QualityController::class, 'recordDefect']);

    // ============ Costing ============
    Route::get('/costing/standard-costs', [\Costing\Presentation\Http\Controllers\CostingController::class, 'standardCosts']);
    Route::post('/costing/standard-costs', [\Costing\Presentation\Http\Controllers\CostingController::class, 'setStandardCost']);
    Route::post('/costing/production-orders/{orderId}/cost', [\Costing\Presentation\Http\Controllers\CostingController::class, 'calculateCost']);
    Route::get('/costing/production-orders/{orderId}/cost', [\Costing\Presentation\Http\Controllers\CostingController::class, 'getCost']);
    Route::get('/costing/production-orders/{orderId}/variance', [\Costing\Presentation\Http\Controllers\CostingController::class, 'variance']);
    Route::get('/costing/variances', [\Costing\Presentation\Http\Controllers\CostingController::class, 'variances']);

    // ============ Sales ============
    Route::apiResource('customers', \Sales\Presentation\Http\Controllers\CustomerController::class);
    Route::apiResource('sales-orders', \Sales\Presentation\Http\Controllers\SalesOrderController::class);
    Route::post('/sales-orders/{id}/confirm', [\Sales\Presentation\Http\Controllers\SalesOrderController::class, 'confirm']);
    Route::post('/sales-orders/{id}/lines', [\Sales\Presentation\Http\Controllers\SalesOrderController::class, 'addLines']);
    Route::get('/sales/atp', [\Sales\Presentation\Http\Controllers\SalesOrderController::class, 'atp']);
    Route::post('/sales-orders/invoice', [\Sales\Presentation\Http\Controllers\SalesOrderController::class, 'createInvoice']);

    // ============ Dispatch ============
    Route::apiResource('dispatches', \Dispatch\Presentation\Http\Controllers\DispatchController::class);
    Route::post('/dispatches/{id}/lines', [\Dispatch\Presentation\Http\Controllers\DispatchController::class, 'addLines']);
    Route::post('/dispatches/{id}/execute', [\Dispatch\Presentation\Http\Controllers\DispatchController::class, 'execute']);
    Route::post('/dispatches/delivery-notes', [\Dispatch\Presentation\Http\Controllers\DispatchController::class, 'createDeliveryNote']);
    Route::post('/dispatches/{id}/mark-delivered', [\Dispatch\Presentation\Http\Controllers\DispatchController::class, 'markDelivered']);

    // ============ Accounting ============
    Route::get('/accounting/chart-of-accounts', [\Accounting\Presentation\Http\Controllers\AccountingController::class, 'chartOfAccounts']);
    Route::post('/accounting/chart-of-accounts', [\Accounting\Presentation\Http\Controllers\AccountingController::class, 'createAccount']);
    Route::get('/accounting/journal-entries', [\Accounting\Presentation\Http\Controllers\AccountingController::class, 'journalEntries']);
    Route::post('/accounting/journal-entries', [\Accounting\Presentation\Http\Controllers\AccountingController::class, 'postJournal']);
    Route::get('/accounting/journal-entries/{id}', [\Accounting\Presentation\Http\Controllers\AccountingController::class, 'getJournal']);
    Route::post('/accounting/journal-entries/{id}/reverse', [\Accounting\Presentation\Http\Controllers\AccountingController::class, 'reverseJournal']);
    Route::get('/accounting/trial-balance', [\Accounting\Presentation\Http\Controllers\AccountingController::class, 'trialBalance']);
    Route::get('/accounting/profit-loss', [\Accounting\Presentation\Http\Controllers\AccountingController::class, 'profitAndLoss']);
    Route::get('/accounting/balance-sheet', [\Accounting\Presentation\Http\Controllers\AccountingController::class, 'balanceSheet']);

    // ============ Health ============
    Route::get('/health', [\Configuration\Presentation\Http\Controllers\HealthController::class, 'check']);
});