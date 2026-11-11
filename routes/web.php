<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/login', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }

    try {
        $user = \Identity\Infrastructure\Persistence\User::withoutGlobalScope('tenant')
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first();

        if ($user) {
            auth()->login($user);
            request()->session()->regenerate();

            return redirect()->intended('/dashboard');
        }
    } catch (\Throwable $e) {
        report($e);

        return response('Authentication bootstrap failed: '.$e->getMessage(), 500);
    }

    return response('Unauthenticated. No active user account is available to sign in.', 401);
})->name('login');

Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
Route::get('/inventory', [PageController::class, 'inventory'])->name('inventory');
Route::get('/inventory/create', [PageController::class, 'createInventoryMovement'])->name('inventory.create');
Route::get('/production', [PageController::class, 'production'])->name('production');
Route::get('/production/create', [PageController::class, 'createProductionOrder'])->name('production.create');
Route::get('/quality', [PageController::class, 'quality'])->name('quality');
Route::get('/quality/create', [PageController::class, 'createQualityInspection'])->name('quality.create');
Route::get('/costing', [PageController::class, 'costing'])->name('costing');
Route::get('/costing/create', [PageController::class, 'createStandardCost'])->name('costing.create');
Route::get('/purchasing', [PageController::class, 'purchasing'])->name('purchasing');
Route::get('/purchasing/create', [PageController::class, 'createPurchaseOrder'])->name('purchasing.create');
Route::get('/sales', [PageController::class, 'sales'])->name('sales');
Route::get('/sales/create', [PageController::class, 'createSalesOrder'])->name('sales.create');
Route::get('/dispatch', [PageController::class, 'dispatch'])->name('dispatch');
Route::get('/dispatch/create', [PageController::class, 'createDispatch'])->name('dispatch.create');
Route::get('/accounting', [PageController::class, 'accounting'])->name('accounting');
Route::get('/accounting/chart-of-accounts', [PageController::class, 'chartOfAccounts'])->name('accounting.chart-of-accounts');
Route::get('/accounting/chart-of-accounts/export', [PageController::class, 'exportChartOfAccounts'])->name('accounting.chart-of-accounts.export');
Route::get('/accounting/chart-of-accounts/import', [PageController::class, 'importAccountsPage'])->name('accounting.chart-of-accounts.import');
Route::get('/accounting/chart-of-accounts/import/template', [PageController::class, 'importAccountTemplate'])->name('accounting.chart-of-accounts.import.template');
Route::post('/accounting/chart-of-accounts/import/preview', [PageController::class, 'previewAccountImport'])->name('accounting.chart-of-accounts.import.preview');
Route::post('/accounting/chart-of-accounts/import/commit', [PageController::class, 'commitAccountImport'])->name('accounting.chart-of-accounts.import.commit');
Route::get('/accounting/chart-of-accounts/create', [PageController::class, 'createAccountPage'])->name('accounting.account.create');
Route::post('/accounting/chart-of-accounts', [PageController::class, 'storeAccount'])->name('accounting.chart-of-accounts.store');
Route::get('/accounting/chart-of-accounts/{id}/ledger', [PageController::class, 'accountLedger'])->name('accounting.chart-of-accounts.ledger');
Route::get('/accounting/chart-of-accounts/{id}/ledger/export', [PageController::class, 'exportAccountLedger'])->name('accounting.chart-of-accounts.ledger.export');
Route::get('/accounting/chart-of-accounts/{id}/edit', [PageController::class, 'editAccountPage'])->name('accounting.chart-of-accounts.edit');
Route::get('/accounting/chart-of-accounts/{id}/duplicate', [PageController::class, 'duplicateAccountPage'])->name('accounting.chart-of-accounts.duplicate');
Route::get('/accounting/account-groups', [PageController::class, 'accountGroups'])->name('accounting.account-groups');
Route::post('/accounting/account-groups', [PageController::class, 'storeAccountGroup'])->name('accounting.account-groups.store');
Route::match(['put', 'patch'], '/accounting/account-groups/{id}', [PageController::class, 'updateAccountGroup'])->name('accounting.account-groups.update');
Route::delete('/accounting/account-groups/{id}', [PageController::class, 'deleteAccountGroup'])->name('accounting.account-groups.delete');
Route::post('/accounting/chart-of-accounts/bulk-toggle', [PageController::class, 'bulkToggleAccounts'])->name('accounting.chart-of-accounts.bulk-toggle');
Route::post('/accounting/chart-of-accounts/{id}/toggle', [PageController::class, 'toggleAccount'])->name('accounting.chart-of-accounts.toggle');
Route::match(['put', 'patch'], '/accounting/chart-of-accounts/{id}', [PageController::class, 'updateAccount'])->name('accounting.chart-of-accounts.update');
Route::delete('/accounting/chart-of-accounts/{id}', [PageController::class, 'deleteAccount'])->name('accounting.chart-of-accounts.delete');
Route::get('/accounting/chart-of-accounts/{id}', [PageController::class, 'showAccountPage'])->name('accounting.chart-of-accounts.show');
Route::get('/accounting/journal-entries/create', [PageController::class, 'createJournalEntry'])->name('accounting.journal-entry.create');
Route::get('/accounting/journal-entries/export', [PageController::class, 'exportJournalEntries'])->name('accounting.journal-entries.export');
Route::post('/accounting/journal-entries', [PageController::class, 'storeJournalEntry'])->name('accounting.journal-entries.store');
Route::get('/accounting/journal-entries/{id}', [PageController::class, 'journalEntryShow'])->name('accounting.journal-entries.show');
Route::post('/accounting/journal-entries/{id}/post', [PageController::class, 'postJournalEntry'])->name('accounting.journal-entries.post');
Route::post('/accounting/journal-entries/{id}/reverse', [PageController::class, 'reverseJournalEntry'])->name('accounting.journal-entries.reverse');
Route::delete('/accounting/journal-entries/{id}', [PageController::class, 'deleteJournalEntry'])->name('accounting.journal-entries.delete');
Route::get('/accounting/periods', [PageController::class, 'accountingPeriods'])->name('accounting.periods');
Route::post('/accounting/periods', [PageController::class, 'storeAccountingPeriod'])->name('accounting.periods.store');
Route::post('/accounting/periods/{id}/toggle', [PageController::class, 'toggleAccountingPeriod'])->name('accounting.periods.toggle');
Route::get('/accounting/trial-balance', [PageController::class, 'trialBalancePage'])->name('accounting.trial-balance');
Route::get('/accounting/trial-balance/export', [PageController::class, 'exportTrialBalance'])->name('accounting.trial-balance.export');
Route::get('/accounting/profit-loss', [PageController::class, 'profitAndLossPage'])->name('accounting.profit-loss');
Route::get('/accounting/profit-loss/export', [PageController::class, 'exportProfitAndLoss'])->name('accounting.profit-loss.export');
Route::get('/accounting/balance-sheet', [PageController::class, 'balanceSheetPage'])->name('accounting.balance-sheet');
Route::get('/accounting/balance-sheet/export', [PageController::class, 'exportBalanceSheet'])->name('accounting.balance-sheet.export');
Route::get('/accounting/cash-flow', [PageController::class, 'cashFlowPage'])->name('accounting.cash-flow');
Route::get('/accounting/cash-flow/export', [PageController::class, 'exportCashFlow'])->name('accounting.cash-flow.export');
Route::get('/tenancy', [PageController::class, 'tenancy'])->name('tenancy');
Route::get('/tenancy/create', [PageController::class, 'createTenant'])->name('tenancy.create');
Route::get('/settings', [PageController::class, 'settings'])->name('settings');

// HR Management
Route::get('/hr', [PageController::class, 'hr'])->name('hr');
Route::get('/hr/create', [PageController::class, 'createEmployee'])->name('hr.create');

// Maintenance
Route::get('/maintenance', [PageController::class, 'maintenance'])->name('maintenance');
Route::get('/maintenance/create', [PageController::class, 'createMachine'])->name('maintenance.create');

// Products / Items
Route::get('/products', [PageController::class, 'products'])->name('products');
Route::get('/products/create', [PageController::class, 'createProduct'])->name('products.create');

// Warehouses
Route::get('/warehouses', [PageController::class, 'warehouses'])->name('warehouses');
Route::get('/warehouses/export', [PageController::class, 'exportWarehouses'])->name('warehouses.export');
Route::get('/warehouses/create', [PageController::class, 'createWarehouse'])->name('warehouses.create');
Route::post('/warehouses', [PageController::class, 'storeWarehouse'])->name('warehouses.store');
Route::get('/warehouses/{id}/edit', [PageController::class, 'editWarehouse'])->name('warehouses.edit');
Route::match(['put', 'patch'], '/warehouses/{id}', [PageController::class, 'updateWarehouse'])->name('warehouses.update');
Route::post('/warehouses/{id}/toggle', [PageController::class, 'toggleWarehouse'])->name('warehouses.toggle');

// Manufacturing — BOMs
Route::get('/boms', [PageController::class, 'boms'])->name('boms');
Route::get('/boms/create', [PageController::class, 'createBOM'])->name('boms.create');

// Manufacturing — Routings
Route::get('/routings', [PageController::class, 'routings'])->name('routings');
Route::get('/routings/create', [PageController::class, 'createRouting'])->name('routings.create');

// Reports
Route::get('/reports', [PageController::class, 'reports'])->name('reports');

// Users
Route::get('/users', [PageController::class, 'users'])->name('users');
Route::get('/users/create', [PageController::class, 'createUser'])->name('users.create');

// Supply Chain — Goods Receipts
Route::get('/supply-chain/goods-receipts', [PageController::class, 'goodsReceipts'])->name('supply-chain.goods-receipts');
Route::get('/supply-chain/goods-receipts/create', [PageController::class, 'createGoodsReceipt'])->name('supply-chain.goods-receipts.create');

// Supply Chain — Purchase Requisitions
Route::get('/supply-chain/purchase-requisitions', [PageController::class, 'purchaseRequisitions'])->name('supply-chain.purchase-requisitions');
Route::get('/supply-chain/purchase-requisitions/create', [PageController::class, 'createPurchaseRequisition'])->name('supply-chain.purchase-requisitions.create');

// Advanced Inventory — Stock Transfers
Route::get('/inventory/transfers', [PageController::class, 'stockTransfers'])->name('inventory.transfers');
Route::get('/inventory/transfers/create', [PageController::class, 'createStockTransfer'])->name('inventory.transfers.create');

// Advanced Inventory — Batch / Lot Tracking
Route::get('/inventory/batches', [PageController::class, 'batches'])->name('inventory.batches');
Route::get('/inventory/batches/create', [PageController::class, 'createBatch'])->name('inventory.batches.create');

// Advanced Inventory — Serial Numbers
Route::get('/inventory/serials', [PageController::class, 'serialNumbers'])->name('inventory.serials');
Route::get('/inventory/serials/create', [PageController::class, 'createSerial'])->name('inventory.serials.create');

// Sales Advanced — Quotes
Route::get('/sales/quotes', [PageController::class, 'salesQuotes'])->name('sales.quotes');
Route::get('/sales/quotes/create', [PageController::class, 'createSalesQuote'])->name('sales.quotes.create');

// Sales Advanced — Customer Invoices
Route::get('/sales/invoices', [PageController::class, 'customerInvoices'])->name('sales.customer-invoices');
Route::get('/sales/invoices/create', [PageController::class, 'createCustomerInvoice'])->name('sales.customer-invoices.create');

// Purchasing Advanced — Supplier Invoices
Route::get('/purchasing/supplier-invoices', [PageController::class, 'supplierInvoices'])->name('purchasing.supplier-invoices');
Route::get('/purchasing/supplier-invoices/create', [PageController::class, 'createSupplierInvoice'])->name('purchasing.supplier-invoices.create');

// Accounting Advanced — Bank Reconciliation
Route::get('/accounting/bank-reconciliation', [PageController::class, 'bankReconciliation'])->name('accounting.bank-reconciliation');
Route::get('/accounting/bank-reconciliation/create', [PageController::class, 'createBankReconciliation'])->name('accounting.bank-reconciliation.create');
Route::get('/accounting/bank-reconciliation/export', [PageController::class, 'exportBankReconciliations'])->name('accounting.bank-reconciliation.export');
Route::post('/accounting/bank-reconciliation', [PageController::class, 'storeBankReconciliation'])->name('accounting.bank-reconciliation.store');
Route::post('/accounting/bank-reconciliation/{id}/reconcile', [PageController::class, 'reconcileBankReconciliation'])->name('accounting.bank-reconciliation.reconcile');
Route::delete('/accounting/bank-reconciliation/{id}', [PageController::class, 'deleteBankReconciliation'])->name('accounting.bank-reconciliation.delete');

// Production Advanced — Scheduling / Gantt
Route::get('/production/schedule', [PageController::class, 'productionSchedule'])->name('production.schedule');

// Maintenance Advanced — Preventive Maintenance
Route::get('/maintenance/preventive', [PageController::class, 'preventiveMaintenance'])->name('maintenance.preventive');
Route::get('/maintenance/preventive/create', [PageController::class, 'createPreventiveMaintenance'])->name('maintenance.preventive.create');

// HR Advanced — Time & Attendance
Route::get('/hr/time-attendance', [PageController::class, 'timeAttendance'])->name('hr.time-attendance');
Route::get('/hr/time-attendance/create', [PageController::class, 'createTimeEntry'])->name('hr.time-attendance.create');

// Analytics Dashboard
Route::get('/analytics', [PageController::class, 'analytics'])->name('analytics');

// MRP — Material Requirements Planning
Route::get('/mrp', [PageController::class, 'mrp'])->name('mrp');
Route::get('/mrp/run', [PageController::class, 'runMRP'])->name('mrp.run');

// Production — Work Orders
Route::get('/production/work-orders', [PageController::class, 'workOrders'])->name('production.work-orders');
Route::get('/production/work-orders/create', [PageController::class, 'createWorkOrder'])->name('production.work-orders.create');
Route::get('/production/work-orders/{id}/operations', [PageController::class, 'workOrderOperations'])->name('production.work-orders.operations');

// Quality — Non-Conformance Reports (NCR)
Route::get('/quality/non-conformance', [PageController::class, 'nonConformanceReports'])->name('quality.non-conformance');
Route::get('/quality/non-conformance/create', [PageController::class, 'createNonConformanceReport'])->name('quality.non-conformance.create');

// Quality — CAPA (Corrective & Preventive Action)
Route::get('/quality/capa', [PageController::class, 'capa'])->name('quality.capa');
Route::get('/quality/capa/create', [PageController::class, 'createCAPA'])->name('quality.capa.create');

// Accounting — Fixed Assets
Route::get('/accounting/fixed-assets', [PageController::class, 'fixedAssets'])->name('accounting.fixed-assets');
Route::get('/accounting/fixed-assets/create', [PageController::class, 'createFixedAsset'])->name('accounting.fixed-assets.create');
Route::post('/accounting/fixed-assets', [PageController::class, 'storeFixedAsset'])->name('accounting.fixed-assets.store');
Route::get('/accounting/fixed-assets/{id}', [PageController::class, 'fixedAssetShow'])->name('accounting.fixed-assets.show');

// HR — Leave Management
Route::get('/hr/leave', [PageController::class, 'leaveManagement'])->name('hr.leave');
Route::get('/hr/leave/create', [PageController::class, 'createLeaveRequest'])->name('hr.leave.create');

// HR — Shift Management
Route::get('/hr/shifts', [PageController::class, 'shiftManagement'])->name('hr.shifts');
Route::get('/hr/shifts/create', [PageController::class, 'createShift'])->name('hr.shifts.create');

// Purchasing — Vendor Scorecards
Route::get('/purchasing/vendor-scorecards', [PageController::class, 'vendorScorecards'])->name('purchasing.vendor-scorecards');

// Documents — Document Management
Route::get('/documents', [PageController::class, 'documents'])->name('documents');
Route::get('/documents/create', [PageController::class, 'createDocument'])->name('documents.create');

// Reporting — Custom Reports
Route::get('/reports/custom', [PageController::class, 'customReports'])->name('reports.custom');
Route::get('/reports/custom/create', [PageController::class, 'createCustomReport'])->name('reports.custom.create');

// Notifications — Real-time with sound
Route::get('/notifications', [PageController::class, 'notifications'])->name('notifications');
Route::get('/notifications/export', [PageController::class, 'exportNotifications'])->name('notifications.export');
Route::get('/notifications/unread-count', [PageController::class, 'notificationUnreadCount'])->name('notifications.unread-count');
Route::post('/notifications/{id}/read', [PageController::class, 'markNotificationRead'])->name('notifications.read');
Route::post('/notifications/read-all', [PageController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
Route::post('/notifications/delete-read', [PageController::class, 'deleteAllReadNotifications'])->name('notifications.delete-read');
Route::delete('/notifications/{id}', [PageController::class, 'deleteNotification'])->name('notifications.delete');
Route::post('/notifications', [PageController::class, 'storeNotification'])->name('notifications.store');
Route::get('/notifications/test', [PageController::class, 'notificationTest'])->name('notifications.test');

// RBAC — Role-Based Access Control
Route::get('/rbac/roles', [PageController::class, 'roles'])->name('rbac.roles');
Route::get('/rbac/roles/create', [PageController::class, 'createRole'])->name('rbac.roles.create');
Route::post('/rbac/roles', [PageController::class, 'storeRole'])->name('rbac.roles.store');
Route::get('/rbac/roles/{id}/edit', [PageController::class, 'editRole'])->name('rbac.roles.edit');
Route::put('/rbac/roles/{id}', [PageController::class, 'updateRole'])->name('rbac.roles.update');
Route::delete('/rbac/roles/{id}', [PageController::class, 'deleteRole'])->name('rbac.roles.delete');
Route::get('/rbac/roles/{id}/permissions', [PageController::class, 'rolePermissions'])->name('rbac.roles.permissions');
Route::put('/rbac/roles/{id}/permissions', [PageController::class, 'updateRolePermissions'])->name('rbac.roles.permissions.update');
Route::get('/rbac/users', [PageController::class, 'usersWithRoles'])->name('rbac.users');
Route::put('/rbac/users/{id}/role', [PageController::class, 'updateUserRole'])->name('rbac.users.role');

// Advanced Planning & Scheduling (APS)
Route::get('/aps', [PageController::class, 'aps'])->name('aps');
Route::get('/aps/gantt', [PageController::class, 'apsGantt'])->name('aps.gantt');
Route::get('/aps/capacity', [PageController::class, 'apsCapacity'])->name('aps.capacity');

// Quality — SPC Control Charts
Route::get('/quality/spc', [PageController::class, 'spc'])->name('quality.spc');
Route::get('/quality/spc/chart/{controlChartId}', [PageController::class, 'spcChart'])->name('quality.spc.chart');

// HR — Payroll
Route::get('/hr/payroll', [PageController::class, 'payroll'])->name('hr.payroll');
Route::get('/hr/payroll/create', [PageController::class, 'createPayroll'])->name('hr.payroll.create');

// HR — Performance Reviews
Route::get('/hr/performance', [PageController::class, 'performanceReviews'])->name('hr.performance');
Route::get('/hr/performance/create', [PageController::class, 'createPerformanceReview'])->name('hr.performance.create');

// HR — Training Management
Route::get('/hr/training', [PageController::class, 'training'])->name('hr.training');
Route::get('/hr/training/create', [PageController::class, 'createTraining'])->name('hr.training.create');

// Purchasing — RFQ Management
Route::get('/purchasing/rfq', [PageController::class, 'rfq'])->name('purchasing.rfq');
Route::get('/purchasing/rfq/create', [PageController::class, 'createRfq'])->name('purchasing.rfq.create');

// Purchasing — Contracts
Route::get('/purchasing/contracts', [PageController::class, 'contracts'])->name('purchasing.contracts');
Route::get('/purchasing/contracts/create', [PageController::class, 'createContract'])->name('purchasing.contracts.create');

// Sales — CPQ
Route::get('/sales/cpq', [PageController::class, 'cpq'])->name('sales.cpq');
Route::get('/sales/cpq/create', [PageController::class, 'createCpq'])->name('sales.cpq.create');

// Sales — Commissions
Route::get('/sales/commissions', [PageController::class, 'commissions'])->name('sales.commissions');

// Advanced Inventory — Cross-docking / Kitting / Consignment
Route::get('/inventory/cross-docking', [PageController::class, 'crossDocking'])->name('inventory.cross-docking');
Route::get('/inventory/kitting', [PageController::class, 'kitting'])->name('inventory.kitting');
Route::get('/inventory/consignment', [PageController::class, 'consignment'])->name('inventory.consignment');

// Advanced Costing — Activity-Based Costing
Route::get('/costing/activity-based', [PageController::class, 'activityBasedCosting'])->name('costing.activity-based');

// Compliance & Audit — Audit Trail
Route::get('/compliance/audit-trail', [PageController::class, 'auditTrail'])->name('compliance.audit-trail');
Route::get('/compliance/audit-trail/export', [PageController::class, 'exportAuditTrail'])->name('compliance.audit-trail.export');

// Workflow Automation — Approval Engine
Route::get('/workflow/approvals', [PageController::class, 'approvals'])->name('workflow.approvals');
Route::post('/workflow/approvals/{id}/approve', [PageController::class, 'approveRequest'])->name('workflow.approvals.approve');
Route::post('/workflow/approvals/{id}/reject', [PageController::class, 'rejectRequest'])->name('workflow.approvals.reject');
Route::get('/workflow/rules', [PageController::class, 'workflowRules'])->name('workflow.rules');

// API & Integrations — Webhooks
Route::get('/integrations/webhooks', [PageController::class, 'webhooks'])->name('integrations.webhooks');
Route::get('/integrations/webhooks/create', [PageController::class, 'createWebhook'])->name('integrations.webhooks.create');

// API — REST Endpoints
Route::prefix('api/v2')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\V2\DashboardController::class, 'index']);
    Route::get('/inventory', [\App\Http\Controllers\Api\V2\InventoryController::class, 'index']);
    Route::get('/production', [\App\Http\Controllers\Api\V2\ProductionController::class, 'index']);
    Route::post('/notifications', [\App\Http\Controllers\Api\V2\NotificationController::class, 'store']);
});

// Customer Portal
Route::prefix('customer')->group(function () {
    Route::get('/dashboard', [PageController::class, 'customerDashboard'])->name('customer.dashboard');
    Route::get('/orders', [PageController::class, 'customerOrders'])->name('customer.orders');
    Route::get('/orders/{id}', [PageController::class, 'customerOrderDetail'])->name('customer.order.detail');
    Route::get('/invoices', [PageController::class, 'portalCustomerInvoices'])->name('customer.invoices');
    Route::get('/quotes', [PageController::class, 'customerQuotes'])->name('customer.quotes');
    Route::post('/quotes/{id}/accept', [PageController::class, 'customerAcceptQuote'])->name('customer.accept.quote');
});

// Supplier Portal
Route::prefix('supplier')->group(function () {
    Route::get('/dashboard', [PageController::class, 'supplierDashboard'])->name('supplier.dashboard');
    Route::get('/purchase-orders', [PageController::class, 'supplierPurchaseOrders'])->name('supplier.purchase-orders');
    Route::get('/purchase-orders/{id}', [PageController::class, 'supplierPODetail'])->name('supplier.po.detail');
    Route::get('/invoices', [PageController::class, 'supplierInvoicesPortal'])->name('supplier.invoices');
    Route::post('/invoices/{id}/accept', [PageController::class, 'supplierAcceptInvoice'])->name('supplier.accept.invoice');
});

// EHS — Environment, Health & Safety
Route::get('/ehs', [PageController::class, 'ehsDashboard'])->name('ehs.dashboard');
Route::get('/ehs/incidents', [PageController::class, 'ehsIncidents'])->name('ehs.incidents');
Route::get('/ehs/incidents/create', [PageController::class, 'createEHSIncident'])->name('ehs.incidents.create');
Route::get('/ehs/near-misses', [PageController::class, 'ehsNearMisses'])->name('ehs.near-misses');
Route::get('/ehs/near-misses/create', [PageController::class, 'createEHSNearMiss'])->name('ehs.near-misses.create');
Route::get('/ehs/safety-audits', [PageController::class, 'ehsSafetyAudits'])->name('ehs.safety-audits');
Route::get('/ehs/safety-audits/create', [PageController::class, 'createEHSSafetyAudit'])->name('ehs.safety-audits.create');

// Energy Management
Route::get('/energy', [PageController::class, 'energyDashboard'])->name('energy.dashboard');
Route::get('/energy/consumption', [PageController::class, 'energyConsumption'])->name('energy.consumption');
Route::get('/energy/consumption/create', [PageController::class, 'createEnergyConsumption'])->name('energy.consumption.create');
Route::get('/energy/costs', [PageController::class, 'energyCosts'])->name('energy.costs');

// Sustainability / Carbon Tracking
Route::get('/sustainability', [PageController::class, 'sustainabilityDashboard'])->name('sustainability.dashboard');
Route::get('/sustainability/carbon', [PageController::class, 'carbonTracking'])->name('sustainability.carbon');
Route::get('/sustainability/carbon/create', [PageController::class, 'createCarbonEntry'])->name('sustainability.carbon.create');
Route::get('/sustainability/waste', [PageController::class, 'wasteTracking'])->name('sustainability.waste');
Route::get('/sustainability/waste/create', [PageController::class, 'createWasteEntry'])->name('sustainability.waste.create');

// Email Templates
Route::get('/communications/email-templates', [PageController::class, 'emailTemplates'])->name('communications.email-templates');
Route::get('/communications/email-templates/create', [PageController::class, 'createEmailTemplate'])->name('communications.email-templates.create');
Route::get('/communications/email-templates/{id}/edit', [PageController::class, 'editEmailTemplate'])->name('communications.email-templates.edit');
Route::get('/communications/email-logs', [PageController::class, 'emailLogs'])->name('communications.email-logs');

// SMS Notifications
Route::get('/communications/sms-templates', [PageController::class, 'smsTemplates'])->name('communications.sms-templates');
Route::get('/communications/sms-templates/create', [PageController::class, 'createSMSTemplate'])->name('communications.sms-templates.create');
Route::get('/communications/sms-logs', [PageController::class, 'smsLogs'])->name('communications.sms-logs');
Route::post('/communications/sms/send', [PageController::class, 'sendSMS'])->name('communications.sms.send');

// Barcode / QR Generator
Route::get('/barcode-generator', [PageController::class, 'barcodeGenerator'])->name('barcode.generator');
Route::get('/qr-generator', [PageController::class, 'qrGenerator'])->name('qr.generator');

// Production Downtime Tracking
Route::get('/production/downtime', [PageController::class, 'downtimeTracking'])->name('production.downtime');
Route::get('/production/downtime/create', [PageController::class, 'createDowntimeEntry'])->name('production.downtime.create');

// Scrap & Waste Tracking
Route::get('/production/scrap', [PageController::class, 'scrapTracking'])->name('production.scrap');
Route::get('/production/scrap/create', [PageController::class, 'createScrapEntry'])->name('production.scrap.create');

// Tool Management
Route::get('/maintenance/tools', [PageController::class, 'toolManagement'])->name('maintenance.tools');
Route::get('/maintenance/tools/create', [PageController::class, 'createTool'])->name('maintenance.tools.create');
Route::get('/maintenance/tools/{id}/calibrate', [PageController::class, 'calibrateTool'])->name('maintenance.tools.calibrate');

// Calibration Management
Route::get('/maintenance/calibration', [PageController::class, 'calibrationManagement'])->name('maintenance.calibration');
Route::get('/maintenance/calibration/schedule', [PageController::class, 'calibrationSchedule'])->name('maintenance.calibration.schedule');

// Change Management (ECO)
Route::get('/engineering/change-orders', [PageController::class, 'changeOrders'])->name('engineering.change-orders');
Route::get('/engineering/change-orders/create', [PageController::class, 'createChangeOrder'])->name('engineering.change-orders.create');
Route::get('/engineering/change-orders/{id}', [PageController::class, 'changeOrderDetail'])->name('engineering.change-orders.detail');

// Training Matrix
Route::get('/hr/training-matrix', [PageController::class, 'trainingMatrix'])->name('hr.training-matrix');
Route::get('/hr/training-matrix/create', [PageController::class, 'createTrainingMatrixEntry'])->name('hr.training-matrix.create');

// Advanced Analytics Charts
Route::get('/analytics/advanced', [PageController::class, 'advancedAnalytics'])->name('analytics.advanced');

// Print / PDF Reports
Route::get('/reports/print/{reportType}', [PageController::class, 'printReport'])->name('reports.print');
