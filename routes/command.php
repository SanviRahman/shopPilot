<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

$redirectWithToast = function (string $type, string $message) {
    $returnTo = trim((string) request()->query('return_to', ''));
    $fallbackUrl = url()->previous() ?: route('admin.dashboard');
    $targetUrl = $fallbackUrl;

    $validRelativeReturn = $returnTo !== ''
        && str_starts_with($returnTo, '/')
        && ! str_starts_with($returnTo, '//');

    if ($validRelativeReturn) {
        $targetUrl = url($returnTo);
    }

    $separator = str_contains($targetUrl, '?') ? '&' : '?';

    return redirect()->to($targetUrl.$separator.http_build_query([
        'toast_type' => $type,
        'toast_message' => $message,
    ]));
};

Route::prefix('command')
    ->name('command.')
    ->middleware(['auth:admin', 'role:admin|super_admin,admin'])
    ->group(function () use ($redirectWithToast) {
        Route::get('/', function () {
            abort_unless(auth('admin')->user()?->can('settings.update'), 403);

            return view('backoffice.admin.commands.index', [
                'title' => 'System Commands',
                'isLocal' => app()->environment('local'),
            ]);
        })->name('index');

        Route::post('/clear-cache', function () use ($redirectWithToast) {
            abort_unless(auth('admin')->user()?->can('settings.update'), 403);
            Artisan::call('cache:clear');

            return $redirectWithToast('success', 'Cache cleared successfully.');
        })->name('clear-cache');

        Route::post('/clear-config', function () use ($redirectWithToast) {
            abort_unless(auth('admin')->user()?->can('settings.update'), 403);
            Artisan::call('config:clear');

            return $redirectWithToast('success', 'Config cleared successfully.');
        })->name('clear-config');

        Route::post('/clear-route', function () use ($redirectWithToast) {
            abort_unless(auth('admin')->user()?->can('settings.update'), 403);
            Artisan::call('route:clear');

            return $redirectWithToast('success', 'Route cache cleared successfully.');
        })->name('clear-route');

        Route::post('/clear-view', function () use ($redirectWithToast) {
            abort_unless(auth('admin')->user()?->can('settings.update'), 403);
            Artisan::call('view:clear');

            return $redirectWithToast('success', 'View cache cleared successfully.');
        })->name('clear-view');

        Route::post('/optimize-clear', function () use ($redirectWithToast) {
            abort_unless(auth('admin')->user()?->can('settings.update'), 403);
            Artisan::call('optimize:clear');

            return $redirectWithToast('success', 'Optimize cache cleared successfully.');
        })->name('optimize-clear');

        Route::post('/migrate', function () use ($redirectWithToast) {
            abort_unless(auth('admin')->user()?->can('settings.update'), 403);
            if (! app()->environment('local')) {
                return $redirectWithToast('error', 'Migrate is allowed only in local environment.');
            }

            Artisan::call('migrate', ['--force' => true]);

            return $redirectWithToast('success', 'Database migrated successfully.');
        })->name('migrate');

        Route::post('/seed', function () use ($redirectWithToast) {
            abort_unless(auth('admin')->user()?->can('settings.update'), 403);
            if (! app()->environment('local')) {
                return $redirectWithToast('error', 'Seed is allowed only in local environment.');
            }

            Artisan::call('db:seed', ['--force' => true]);

            return $redirectWithToast('success', 'Database seeded successfully.');
        })->name('seed');

        Route::post('/migrate-fresh', function () use ($redirectWithToast) {
            abort_unless(auth('admin')->user()?->can('settings.update'), 403);
            if (! app()->environment('local')) {
                return $redirectWithToast('error', 'Fresh migrate is allowed only in local environment.');
            }

            Artisan::call('migrate:fresh', ['--force' => true]);

            return $redirectWithToast('success', 'Database fresh migrated successfully.');
        })->name('migrate-fresh');

        Route::post('/migrate-fresh-seed', function () use ($redirectWithToast) {
            abort_unless(auth('admin')->user()?->can('settings.update'), 403);
            if (! app()->environment('local')) {
                return $redirectWithToast('error', 'Fresh migrate seed is allowed only in local environment.');
            }

            Artisan::call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);

            return $redirectWithToast('success', 'Database fresh migrated and seeded successfully.');
        })->name('migrate-fresh-seed');
    });