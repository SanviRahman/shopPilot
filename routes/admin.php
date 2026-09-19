<?php

use App\Http\Controllers\Backoffice\Admin\AdminController;
use App\Http\Controllers\Backoffice\Admin\DashboardController;
use App\Http\Controllers\Backoffice\Admin\PermissionController;
use App\Http\Controllers\Backoffice\Admin\RoleController;
use App\Http\Controllers\Backoffice\Admin\CategoryController;

use App\Http\Controllers\Backoffice\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('guest:admin')->group(function () {
            Route::get('login', [LoginController::class, 'create'])->name('login');
            Route::post('login', [LoginController::class, 'store'])
                ->middleware('throttle:5,1')
                ->name('login.store');
        });

        if (is_file(__DIR__ . '/command.php')) {
            require __DIR__ . '/command.php';
        }

        Route::middleware('auth:admin')->group(function () {
            Route::get('redirect', [LoginController::class, 'redirect'])->name('redirect');
            Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/dashboard', [DashboardController::class, 'index']);

            Route::prefix('roles')->name('roles.')->group(function () {
                Route::get('trash', [RoleController::class, 'trash'])->name('trash');
                Route::get('create', [RoleController::class, 'create'])->name('create');
                Route::post('bulk-action', [RoleController::class, 'bulkAction'])->name('bulk-action');
                Route::patch('{role}/restore', [RoleController::class, 'restore'])->name('restore');
                Route::delete('{role}/force-delete', [RoleController::class, 'forceDelete'])->name('force-delete');
                Route::get('{role}/edit', [RoleController::class, 'edit'])->name('edit');
                Route::get('{role}', [RoleController::class, 'show'])->name('show');
                Route::post('/', [RoleController::class, 'store'])->name('store');
                Route::put('{role}', [RoleController::class, 'update'])->name('update');
                Route::delete('{role}', [RoleController::class, 'destroy'])->name('destroy');
                Route::get('/', [RoleController::class, 'index'])->name('index');
            });

            Route::prefix('permissions')->name('permissions.')->group(function () {
                Route::get('trash', [PermissionController::class, 'trash'])->name('trash');
                Route::get('create', [PermissionController::class, 'create'])->name('create');
                Route::post('bulk-action', [PermissionController::class, 'bulkAction'])->name('bulk-action');
                Route::patch('{permission}/restore', [PermissionController::class, 'restore'])->name('restore');
                Route::delete('{permission}/force-delete', [PermissionController::class, 'forceDelete'])->name('force-delete');
                Route::get('{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
                Route::get('{permission}', [PermissionController::class, 'show'])->name('show');
                Route::post('/', [PermissionController::class, 'store'])->name('store');
                Route::put('{permission}', [PermissionController::class, 'update'])->name('update');
                Route::delete('{permission}', [PermissionController::class, 'destroy'])->name('destroy');
                Route::get('/', [PermissionController::class, 'index'])->name('index');
            });

            Route::prefix('admins')->name('admins.')->group(function () {
                Route::get('trash', [AdminController::class, 'trash'])->name('trash');
                Route::get('create', [AdminController::class, 'create'])->name('create');
                Route::post('bulk-action', [AdminController::class, 'bulkAction'])->name('bulk-action');
                Route::patch('{admin}/restore', [AdminController::class, 'restore'])->name('restore');
                Route::delete('{admin}/force-delete', [AdminController::class, 'forceDelete'])->name('force-delete');
                Route::get('{admin}/edit', [AdminController::class, 'edit'])->name('edit');
                Route::get('{admin}', [AdminController::class, 'show'])->name('show');
                Route::post('/', [AdminController::class, 'store'])->name('store');
                Route::put('{admin}', [AdminController::class, 'update'])->name('update');
                Route::delete('{admin}', [AdminController::class, 'destroy'])->name('destroy');
                Route::get('/', [AdminController::class, 'index'])->name('index');
            });

            Route::prefix('categories')->name('categories.')->group(function () {
                Route::get('trash', [CategoryController::class, 'trash'])->name('trash');
                Route::get('create', [CategoryController::class, 'create'])->name('create');
                Route::post('bulk-action', [CategoryController::class, 'bulkAction'])->name('bulk-action');
                Route::post('reorder', [CategoryController::class, 'reorder'])->name('reorder');
                Route::patch('{category}/restore', [CategoryController::class, 'restore'])->name('restore');
                Route::delete('{category}/force-delete', [CategoryController::class, 'forceDelete'])->name('force-delete');
                Route::get('{category}/edit', [CategoryController::class, 'edit'])->name('edit');
                Route::get('{category}', [CategoryController::class, 'show'])->name('show');
                Route::post('/', [CategoryController::class, 'store'])->name('store');
                Route::put('{category}', [CategoryController::class, 'update'])->name('update');
                Route::delete('{category}', [CategoryController::class, 'destroy'])->name('destroy');
                Route::get('/', [CategoryController::class, 'index'])->name('index');
            });
        });
    });
