<?php

use App\Models\MetaPixelEvent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

$redirectWithToast = function (string $type, string $message) {
    $returnTo = trim((string) request()->query('return_to', ''));
    $fallbackUrl = url()->previous() ?: route('admin.dashboard');
    $targetUrl = $fallbackUrl;
    $validRelativeReturn = $returnTo !== '' && str_starts_with($returnTo, '/') && ! str_starts_with($returnTo, '//');

    if ($validRelativeReturn) {
        $targetUrl = url($returnTo);
    }

    $separator = str_contains($targetUrl, '?') ? '&' : '?';

    return redirect()->to($targetUrl.$separator.http_build_query(['toast_type' => $type, 'toast_message' => $message]));
};

Route::prefix('command')->name('command.')->middleware(['auth:admin', 'role:admin|super_admin,admin'])->group(function () use ($redirectWithToast) {
    Route::get('/', function () {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);
        return view('backoffice.admin.commands.index', ['title' => 'System Commands', 'isLocal' => app()->environment('local')]);
    })->name('index');

    Route::post('/clear-cache', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            Artisan::call('cache:clear');
            return $redirectWithToast('success', 'Application cache cleared successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Cache clear failed: '.$exception->getMessage());
        }
    })->name('clear-cache');

    Route::post('/clear-config', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            Artisan::call('config:clear');
            return $redirectWithToast('success', 'Configuration cache cleared successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Config clear failed: '.$exception->getMessage());
        }
    })->name('clear-config');

    Route::post('/clear-route', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            Artisan::call('route:clear');
            return $redirectWithToast('success', 'Route cache cleared successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Route cache clear failed: '.$exception->getMessage());
        }
    })->name('clear-route');

    Route::post('/clear-view', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            Artisan::call('view:clear');
            return $redirectWithToast('success', 'Compiled Blade views cleared successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'View cache clear failed: '.$exception->getMessage());
        }
    })->name('clear-view');

    Route::post('/optimize-clear', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            Artisan::call('optimize:clear');
            return $redirectWithToast('success', 'Application optimization caches cleared successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Optimize clear failed: '.$exception->getMessage());
        }
    })->name('optimize-clear');

    Route::post('/clear-meta-pixel-events', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            $eventCount = MetaPixelEvent::query()->count();

            if ($eventCount === 0) {
                return $redirectWithToast('success', 'Meta Pixel event log is already empty.');
            }

            MetaPixelEvent::query()->delete();

            return $redirectWithToast('success', number_format($eventCount).' Meta Pixel event log(s) cleared successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Meta Pixel event clear failed: '.$exception->getMessage());
        }
    })->name('clear-meta-pixel-events');

    Route::post('/storage-link', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            $exitCode = Artisan::call('storage:link');
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                return $redirectWithToast('error', $output !== '' ? 'Storage link failed: '.$output : 'Storage link could not be created.');
            }

            return $redirectWithToast('success', $output !== '' ? $output : 'Public storage link created successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Storage link failed: '.$exception->getMessage());
        }
    })->name('storage-link');

    Route::post('/storage-link-rebuild', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            try {
                Artisan::call('storage:unlink');
            } catch (\Throwable $exception) {
                report($exception);
            }

            $exitCode = Artisan::call('storage:link');
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                return $redirectWithToast('error', $output !== '' ? 'Storage link rebuild failed: '.$output : 'Storage link could not be rebuilt.');
            }

            return $redirectWithToast('success', 'Public storage link rebuilt successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Storage link rebuild failed: '.$exception->getMessage());
        }
    })->name('storage-link-rebuild');

    Route::post('/migrate', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        if (! app()->environment('local')) {
            return $redirectWithToast('error', 'Migrate is allowed only in local environment.');
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            return $redirectWithToast('success', 'Database migrated successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Migration failed: '.$exception->getMessage());
        }
    })->name('migrate');

    Route::post('/seed', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        if (! app()->environment('local')) {
            return $redirectWithToast('error', 'Seed is allowed only in local environment.');
        }

        try {
            Artisan::call('db:seed', ['--force' => true]);
            return $redirectWithToast('success', 'Database seeded successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Database seed failed: '.$exception->getMessage());
        }
    })->name('seed');

    Route::post('/migrate-fresh', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        if (! app()->environment('local')) {
            return $redirectWithToast('error', 'Fresh migrate is allowed only in local environment.');
        }

        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
            return $redirectWithToast('success', 'Database fresh migrated successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Fresh migration failed: '.$exception->getMessage());
        }
    })->name('migrate-fresh');

    Route::post('/migrate-fresh-seed', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        if (! app()->environment('local')) {
            return $redirectWithToast('error', 'Fresh migrate seed is allowed only in local environment.');
        }

        try {
            Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
            return $redirectWithToast('success', 'Database fresh migrated and seeded successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Fresh migration and seed failed: '.$exception->getMessage());
        }
    })->name('migrate-fresh-seed');
});