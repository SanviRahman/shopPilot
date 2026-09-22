<?php

use App\Http\Controllers\Backoffice\Admin\AdminController;
use App\Http\Controllers\Backoffice\Admin\CategoryController;
use App\Http\Controllers\Backoffice\Admin\DashboardController;
use App\Http\Controllers\Backoffice\Admin\MediaController;
use App\Http\Controllers\Backoffice\Admin\PermissionController;
use App\Http\Controllers\Backoffice\Admin\ProductController;
use App\Http\Controllers\Backoffice\Admin\ProfileController;
use App\Http\Controllers\Backoffice\Admin\RoleController;
use App\Http\Controllers\Backoffice\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    if (is_file(__DIR__ . '/command.php')) {
        require __DIR__ . '/command.php';
    }

    Route::middleware('auth:admin')->group(function () {
        Route::get('redirect', [LoginController::class, 'redirect'])->name('redirect');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
        Route::post('/profile', [ProfileController::class, 'updateProfile'])->name('update_profile');
        Route::get('/password', [ProfileController::class, 'password'])->name('password');
        Route::post('/password', [ProfileController::class, 'updatePassword'])->name('update_password');

        // Roles
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('trash', [RoleController::class, 'trash'])->name('trash');
            Route::post('bulk-action', [RoleController::class, 'bulkAction'])->name('bulk-action');
            Route::patch('{role}/restore', [RoleController::class, 'restore'])->name('restore');
            Route::delete('{role}/force-delete', [RoleController::class, 'forceDelete'])->name('force-delete');
        });
        Route::resource('roles', RoleController::class);

        // Permissions
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('trash', [PermissionController::class, 'trash'])->name('trash');
            Route::post('bulk-action', [PermissionController::class, 'bulkAction'])->name('bulk-action');
            Route::patch('{permission}/restore', [PermissionController::class, 'restore'])->name('restore');
            Route::delete('{permission}/force-delete', [PermissionController::class, 'forceDelete'])->name('force-delete');
        });
        Route::resource('permissions', PermissionController::class);

        //Admins
        Route::prefix('admins')->name('admins.')->group(function () {
            Route::get('trash', [AdminController::class, 'trash'])->name('trash');
            Route::post('bulk-action', [AdminController::class, 'bulkAction'])->name('bulk-action');
            Route::patch('{admin}/restore', [AdminController::class, 'restore'])->name('restore');
            Route::delete('{admin}/force-delete', [AdminController::class, 'forceDelete'])->name('force-delete');
        });
        Route::resource('admins', AdminController::class);

        // Categories
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('trash', [CategoryController::class, 'trash'])->name('trash');
            Route::post('bulk-action', [CategoryController::class, 'bulkAction'])->name('bulk-action');
            Route::post('reorder', [CategoryController::class, 'reorder'])->name('reorder');
            Route::patch('{category}/restore', [CategoryController::class, 'restore'])->name('restore');
            Route::delete('{category}/force-delete', [CategoryController::class, 'forceDelete'])->name('force-delete');
        });
        Route::resource('categories', CategoryController::class);

        // Media
        Route::prefix('media')->name('media.')->group(function () {
            Route::get('trash', [MediaController::class, 'trash'])->name('trash');
            Route::get('picker', [MediaController::class, 'picker'])->name('picker');
            Route::post('bulk-action', [MediaController::class, 'bulkAction'])->name('bulk-action');
            Route::patch('{media}/restore', [MediaController::class, 'restore'])->name('restore');
            Route::delete('{media}/force-delete', [MediaController::class, 'forceDelete'])->name('force-delete');
        });
        Route::resource('media', MediaController::class)->only(['index', 'destroy']);

        // Products
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('trash', [ProductController::class, 'trash'])->name('trash');
            Route::post('bulk-action', [ProductController::class, 'bulkAction'])->name('bulk-action');
            Route::patch('{product}/restore', [ProductController::class, 'restore'])->name('restore');
            Route::delete('{product}/force-delete', [ProductController::class, 'forceDelete'])->name('force-delete');
        });
        Route::resource('products', ProductController::class);

    });
});
