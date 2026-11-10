<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
Route::get('/inventory', [PageController::class, 'inventory'])->name('inventory');
Route::get('/production', [PageController::class, 'production'])->name('production');
Route::get('/quality', [PageController::class, 'quality'])->name('quality');
Route::get('/costing', [PageController::class, 'costing'])->name('costing');
Route::get('/purchasing', [PageController::class, 'purchasing'])->name('purchasing');
Route::get('/sales', [PageController::class, 'sales'])->name('sales');
Route::get('/dispatch', [PageController::class, 'dispatch'])->name('dispatch');
Route::get('/accounting', [PageController::class, 'accounting'])->name('accounting');
Route::get('/tenancy', [PageController::class, 'tenancy'])->name('tenancy');
Route::get('/settings', [PageController::class, 'settings'])->name('settings');
