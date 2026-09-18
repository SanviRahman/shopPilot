<?php

use App\Http\Controllers\Backoffice\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        require __DIR__.'/command.php';

        Route::middleware(['auth', 'role:Admin'])->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/dashboard', [DashboardController::class, 'index']);

            /*
             | Later CRUD (do not uncomment until controllers exist):
             |
             | Route::prefix('categories')->name('categories.')->group(function () {
             |     Route::get('trash', [CategoryController::class, 'trash'])->name('trash');
             |     Route::post('{category}/restore', [CategoryController::class, 'restore'])->name('restore');
             |     Route::delete('{category}/force-delete', [CategoryController::class, 'forceDelete'])->name('force-delete');
             |     Route::resource('/', CategoryController::class)->parameters(['' => 'category']);
             | });
             |
             | Same pattern for: staff, users, products, coupons,
             | payment-methods, orders, payments.
             |
             | Do NOT add Role/Permission trash or force-delete routes.
             */
        });
    });