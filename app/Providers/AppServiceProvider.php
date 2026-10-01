<?php

namespace App\Providers;

use App\Models\Admin;
use App\Observers\AdminObserver;
use Illuminate\Support\Facades\Gate;
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
        
        // Super admin implicitly gets all permissions
        Gate::before(function ($user, $ability) {
            return method_exists($user, 'hasRole')
                && $user->hasRole('super_admin')
                    ? true
                    : null;
        });
    }
}