<?php

use App\Http\Controllers\Backoffice\Manager\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('manager')
    ->name('manager.')
    ->middleware(['auth', 'role:Manager'])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });