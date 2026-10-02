<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Category;
use App\Observers\AdminObserver;
use App\Services\CartService;
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
            $view->with([
                'navigationCategories' => Category::query()
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->limit(10)
                    ->get(['id', 'name', 'slug']),
                'headerCartSummary' => app(CartService::class)->summary(),
            ]);
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