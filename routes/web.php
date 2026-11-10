<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Dashboard route
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'stats' => [
            'production_today' => 1240,
            'raw_material_value' => 4200000,
            'orders_delayed' => 4,
            'finished_goods' => 890,
            'quality_rejection_rate' => 2.1,
            'actual_unit_cost' => 149,
        ],
    ]);
})->name('dashboard');

// Inventory routes
Route::get('/inventory', function () {
    return Inertia::render('Inventory', [
        'movements' => [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'from' => 0,
                'to' => 0,
            ],
        ],
    ]);
})->name('inventory');

// Production routes
Route::get('/production', function () {
    return Inertia::render('Production', [
        'production_orders' => [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'from' => 0,
                'to' => 0,
            ],
        ],
    ]);
})->name('production');

// Quality routes
Route::get('/quality', function () {
    return Inertia::render('Quality', [
        'inspections' => [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'from' => 0,
                'to' => 0,
            ],
        ],
    ]);
})->name('quality');

// Costing routes
Route::get('/costing', function () {
    return Inertia::render('Costing', [
        'variances' => [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'from' => 0,
                'to' => 0,
            ],
        ],
    ]);
})->name('costing');

// Purchasing routes
Route::get('/purchasing', function () {
    return Inertia::render('Purchasing', [
        'suppliers' => [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'from' => 0,
                'to' => 0,
            ],
        ],
    ]);
})->name('purchasing');

// Sales routes
Route::get('/sales', function () {
    return Inertia::render('Sales', [
        'customers' => [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'from' => 0,
                'to' => 0,
            ],
        ],
    ]);
})->name('sales');

// Dispatch routes
Route::get('/dispatch', function () {
    return Inertia::render('Dispatch', [
        'dispatches' => [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'from' => 0,
                'to' => 0,
            ],
        ],
    ]);
})->name('dispatch');

// Accounting routes
Route::get('/accounting', function () {
    return Inertia::render('Accounting', [
        'journal_entries' => [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'from' => 0,
                'to' => 0,
            ],
        ],
    ]);
})->name('accounting');

// Tenancy routes
Route::get('/tenancy', function () {
    return Inertia::render('Tenancy', [
        'tenants' => [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'from' => 0,
                'to' => 0,
            ],
        ],
    ]);
})->name('tenancy');

// Settings route
Route::get('/settings', function () {
    return Inertia::render('Settings');
})->name('settings');
