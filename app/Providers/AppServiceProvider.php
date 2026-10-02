<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Category;
use App\Observers\AdminObserver;
use App\Services\CartService;
use App\Services\Website\WishlistService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {

        Admin::observe(AdminObserver::class);

        View::composer('website.partials.header', function ($view) {
            $wishlistCount = 0;
            if (auth('web')->check()) {
                $wishlistCount = app(WishlistService::class)->count(auth('web')->user());
            }

            $view->with([
                'navigationCategories' => Category::query()
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->limit(10)
                    ->get(['id', 'name', 'slug']),
                'headerCartSummary' => app(CartService::class)->summary(),
                'headerWishlistCount' => $wishlistCount,
            ]);
        });

        View::composer([
            'website.partials.product-card',
            'website.products.show',
            'website.cart.partials.ajax-content',
        ], function ($view) {
            $wishlistProductIds = [];
            if (auth('web')->check()) {
                $request = request();
                if (! $request->attributes->has('shoppilot_wishlist_product_ids')) {
                    $request->attributes->set(
                        'shoppilot_wishlist_product_ids',
                        app(WishlistService::class)->productIds(auth('web')->user()),
                    );
                }
                $wishlistProductIds = (array) $request->attributes->get('shoppilot_wishlist_product_ids', []);
            }

            $view->with('wishlistProductIds', $wishlistProductIds);
        });

        // Super admin implicitly gets all permissions
        Gate::before(function ($user, $ability) {
            return method_exists($user, 'hasRole')
                && $user->hasRole('super_admin')
                    ? true
                    : null;
        });
    }
}