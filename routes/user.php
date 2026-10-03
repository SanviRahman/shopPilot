<?php

use App\Http\Controllers\Website\BlogPageController;
use App\Http\Controllers\Website\ContactPageController;
use App\Http\Controllers\Website\ContentPageController;
use App\Http\Controllers\Website\CartController;
use App\Http\Controllers\Website\CheckoutController;
use App\Http\Controllers\Website\CustomerAccountController;
use App\Http\Controllers\Website\CustomerAuthController;
use App\Http\Controllers\Website\CustomerPaymentController;
use App\Http\Controllers\Website\CustomerPasswordController;
use App\Http\Controllers\Website\CustomerProfileController;
use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\MetaPixelEventController;
use App\Http\Controllers\Website\ProductController;
use App\Http\Controllers\Website\ShopController;
use App\Http\Controllers\Website\TrackOrderController;
use App\Http\Controllers\Website\WishlistController;
use Illuminate\Support\Facades\Route;

Route::name('website.')->group(function () {
    Route::post('/tracking/meta/events', MetaPixelEventController::class)->middleware('throttle:120,1')->name('meta-pixel.events');
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/shop', [ShopController::class, 'index'])->name('shop');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/about', [ContentPageController::class, 'about'])->name('about');
    Route::get('/faq', [ContentPageController::class, 'faq'])->name('faq');
    Route::get('/policies/{slug}', [ContentPageController::class, 'policy'])->whereIn('slug', ['shipping-policy', 'return-refund-policy', 'privacy-policy', 'terms-conditions'])->name('policy');
    Route::get('/contact', [ContactPageController::class, 'index'])->name('contact');
    Route::post('/contact', [ContactPageController::class, 'store'])->middleware('throttle:6,1')->name('contact.store');
    Route::get('/blogs', [BlogPageController::class, 'index'])->name('blogs.index');
    Route::get('/blogs/{slug}', [BlogPageController::class, 'show'])->name('blogs.show');
    Route::get('/track-order', [TrackOrderController::class, 'index'])->name('track-order');
    Route::post('/track-order', [TrackOrderController::class, 'lookup'])->middleware('throttle:20,1')->name('track-order.lookup');

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
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
        Route::post('/wishlist/items', [WishlistController::class, 'store'])->middleware('throttle:60,1')->name('wishlist.store');
        Route::delete('/wishlist/items/{productId}', [WishlistController::class, 'destroy'])->whereNumber('productId')->name('wishlist.destroy');
        Route::delete('/wishlist', [WishlistController::class, 'clear'])->name('wishlist.clear');
        Route::post('/wishlist/add-all-to-cart', [WishlistController::class, 'addAllToCart'])->middleware('throttle:10,1')->name('wishlist.add-all-to-cart');
        Route::get('/profile', [CustomerProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [CustomerProfileController::class, 'update'])->middleware('throttle:20,1')->name('profile.update');
        Route::get('/change-password', [CustomerPasswordController::class, 'edit'])->name('password.edit');
        Route::put('/change-password', [CustomerPasswordController::class, 'update'])->middleware('throttle:10,1')->name('password.update');
    });
});
