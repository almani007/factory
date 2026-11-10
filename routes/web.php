<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Core\Http\Controllers\HomeController;
use App\Modules\Core\Http\Controllers\HealthController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/health', [HealthController::class, 'check'])->name('health');

// Inertia.js frontend routes (handled by Inertia)
// Route::get('/dashboard', function () {
//     return inertia('Dashboard');
// })->name('dashboard');