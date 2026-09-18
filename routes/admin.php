<?php

use App\Http\Controllers\Backoffice\Admin\DashboardController;
use App\Http\Controllers\Backoffice\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('login', [LoginController::class, 'create'])->name('login');
            Route::post('login', [LoginController::class, 'store'])
                ->middleware('throttle:5,1')
                ->name('login.store');
        });

        Route::middleware('auth')->group(function () {
            Route::get('redirect', [LoginController::class, 'redirect'])->name('redirect');
            Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        });

        if (is_file(__DIR__.'/command.php')) {
            require __DIR__.'/command.php';
        }

        Route::middleware(['auth', 'role:Admin'])->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/dashboard', [DashboardController::class, 'index']);
        });
    });