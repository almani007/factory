<?php

use Illuminate\Support\Facades\Route;
use Configuration\Presentation\Http\Controllers\HomeController;
use Configuration\Presentation\Http\Controllers\HealthController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/health', [HealthController::class, 'check'])->name('health');