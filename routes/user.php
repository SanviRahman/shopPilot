<?php

use App\Http\Controllers\Website\CartController;
use App\Http\Controllers\Website\CheckoutController;
use App\Http\Controllers\Website\CustomerAccountController;
use App\Http\Controllers\Website\CustomerAuthController;
use App\Http\Controllers\Website\CustomerPaymentController;
use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\MetaPixelEventController;
use App\Http\Controllers\Website\ProductController;
use App\Http\Controllers\Website\ShopController;
use Illuminate\Support\Facades\Route;

Route::name('website.')->group(function () {
    Route::post('/tracking/meta/events', MetaPixelEventController::class)->middleware('throttle:120,1')->name('meta-pixel.events');
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/shop', [ShopController::class, 'index'])->name('shop');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

    Route::middleware('guest:web')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'login'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'authenticate'])->middleware('throttle:8,1')->name('login.store');
        Route::get('/register', [CustomerAuthController::class, 'register'])->name('register');
        Route::post('/register', [CustomerAuthController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
        Route::get('/forgot-password', [CustomerAuthController::class, 'forgotPassword'])->name('password.request');
        Route::post('/forgot-password', [CustomerAuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
        Route::get('/reset-password/{token}', [CustomerAuthController::class, 'resetPassword'])->name('password.reset');
        Route::post('/reset-password', [CustomerAuthController::class, 'updatePassword'])->name('password.update');
    });

    Route::post('/logout', [CustomerAuthController::class, 'logout'])->middleware('auth:web')->name('logout');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{productId}', [CartController::class, 'update'])->whereNumber('productId')->name('cart.items.update');
    Route::delete('/cart/items/selected', [CartController::class, 'removeSelected'])->name('cart.items.remove-selected');
    Route::delete('/cart/items/{productId}', [CartController::class, 'destroy'])->whereNumber('productId')->name('cart.items.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
    Route::delete('/checkout/coupon', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
    Route::get('/checkout/thank-you/{orderNumber}', [CheckoutController::class, 'thankYou'])->name('checkout.thank-you');

    Route::middleware(['auth:web', 'customer.active'])->prefix('account')->name('account.')->group(function () {
        Route::get('/', [CustomerAccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [CustomerAccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{orderNumber}', [CustomerAccountController::class, 'order'])->name('orders.show');
        Route::get('/payments', [CustomerAccountController::class, 'payments'])->name('payments');
        Route::post('/payments', [CustomerPaymentController::class, 'store'])->middleware('throttle:8,1')->name('payments.store');
    });
});
