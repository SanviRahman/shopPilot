<?php

use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\InventoryController;
use App\Http\Controllers\Vendor\OrderController;
use App\Http\Controllers\Vendor\FinanceController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\ProfileController;
use App\Http\Controllers\Vendor\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('vendor')->name('vendor.')->middleware(['auth:web', 'customer.active', 'vendor.active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{vendorOrder}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{vendorOrder}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::post('finance/payout-accounts', [FinanceController::class, 'storeAccount'])->name('finance.accounts.store');
    Route::post('finance/payout-requests', [FinanceController::class, 'requestPayout'])->name('finance.payouts.store');

    Route::resource('products', ProductController::class)->except(['show']);
    Route::patch('products/{product}/submit-review', [ProductController::class, 'submitForReview'])->name('products.submit-review');
    Route::patch('products/{product}/publish', [ProductController::class, 'publish'])->name('products.publish');
    Route::patch('products/{product}/unpublish', [ProductController::class, 'unpublish'])->name('products.unpublish');

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::patch('inventory/products/{product}', [InventoryController::class, 'updateProductStock'])->name('inventory.products.update');
    Route::patch('inventory/variants/{variant}', [InventoryController::class, 'updateVariantStock'])->name('inventory.variants.update');

    Route::get('store', [StoreController::class, 'edit'])->name('store.edit');
    Route::put('store', [StoreController::class, 'update'])->name('store.update');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
});
