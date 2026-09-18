<?php

use App\Http\Controllers\Agent\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('agent')
    ->name('agent.')
    ->middleware(['auth:admin', 'role:agent,admin'])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });