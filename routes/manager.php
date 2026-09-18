<?php

use App\Http\Controllers\Backoffice\Manager\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('manager')
    ->name('manager.')
    ->middleware(['auth:admin', 'role:manager,admin'])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });