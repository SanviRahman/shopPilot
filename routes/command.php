<?php

use App\Models\MetaPixelEvent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
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

$normalizeStoragePath = static fn (string $path): string => strtolower(str_replace('\\', '/', rtrim($path, '/\\')));

$mediaStorageStatus = function () use ($normalizeStoragePath): array {
    $configuredRoot = (string) config('filesystems.disks.public.root');
    $configuredUrl = (string) config('filesystems.disks.public.url');
    $legacyRoot = storage_path('app/public');
    $documentRoot = trim((string) request()->server('DOCUMENT_ROOT', ''));
    $documentRoot = $documentRoot !== '' ? (realpath($documentRoot) ?: $documentRoot) : '';
    $webStorage = $documentRoot !== '' ? rtrim($documentRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'storage' : '';
    $directPublicDisk = $webStorage !== '' && $normalizeStoragePath($configuredRoot) === $normalizeStoragePath($webStorage);
    $status = 'missing';
    $target = null;

    if ($webStorage !== '' && is_link($webStorage)) {
        $status = 'symlink';
        $target = readlink($webStorage) ?: null;
    } elseif ($webStorage !== '' && is_dir($webStorage)) {
        $status = $directPublicDisk ? 'direct-public-directory' : 'real-directory';
    } elseif ($webStorage !== '' && file_exists($webStorage)) {
        $status = 'real-file';
    }

    return ['configured_root' => $configuredRoot, 'configured_url' => $configuredUrl, 'legacy_root' => $legacyRoot, 'document_root' => $documentRoot, 'web_storage' => $webStorage, 'direct_public_disk' => $directPublicDisk, 'status' => $status, 'target' => $target];
};

$repairMediaStorage = function (bool $force = false) use ($mediaStorageStatus, $normalizeStoragePath): array {
    $status = $mediaStorageStatus();
    $destination = $status['configured_root'];

    if ($destination === '') {
        throw new \RuntimeException('The public disk root is empty. Configure PUBLIC_DISK_ROOT or use the Laravel default public disk.');
    }

    File::ensureDirectoryExists($destination, 0755, true);
    $messages = [];
    $sources = [storage_path('app/public'), public_path('storage')];

    foreach ($sources as $source) {
        if (! is_dir($source) || $normalizeStoragePath($source) === $normalizeStoragePath($destination)) {
            continue;
        }

        if (! File::copyDirectory($source, $destination)) {
            throw new \RuntimeException('Could not copy existing media from '.$source.' to '.$destination);
        }

        $messages[] = 'Synced existing media from '.$source.' to '.$destination;
    }

    if ($status['direct_public_disk']) {
        $messages[] = 'Direct cPanel public storage is active. No symlink is required.';
        return ['status' => $mediaStorageStatus(), 'messages' => $messages];
    }

    $webStorage = $status['web_storage'];

    if ($webStorage === '') {
        throw new \RuntimeException('The web server DOCUMENT_ROOT could not be detected.');
    }

    if (is_link($webStorage)) {
        $currentTarget = readlink($webStorage) ?: '';

        if (! $force && $currentTarget !== '' && $normalizeStoragePath(realpath($webStorage) ?: $currentTarget) === $normalizeStoragePath(realpath($destination) ?: $destination)) {
            $messages[] = 'Storage symlink is already correct.';
            return ['status' => $mediaStorageStatus(), 'messages' => $messages];
        }

        if (! @unlink($webStorage)) {
            throw new \RuntimeException('Could not remove the existing storage symlink: '.$webStorage);
        }
    } elseif (file_exists($webStorage)) {
        if (! $force) {
            throw new \RuntimeException('The web root already contains a real storage file/folder. Use Force Repair Media Storage to back it up safely.');
        }

        $backup = $webStorage.'_backup_'.date('Ymd_His');

        if (! @rename($webStorage, $backup)) {
            throw new \RuntimeException('Could not back up the existing web storage path: '.$webStorage);
        }

        $messages[] = 'Existing web storage backed up as '.basename($backup);
    }

    if (! @symlink(realpath($destination) ?: $destination, $webStorage)) {
        $error = error_get_last();
        throw new \RuntimeException('Could not create the web storage symlink. '.($error['message'] ?? 'The host may have disabled PHP symlink().'));
    }

    $messages[] = 'Linked '.$webStorage.' → '.$destination;

    return ['status' => $mediaStorageStatus(), 'messages' => $messages];
};

Route::prefix('command')->name('command.')->middleware(['auth:admin', 'role:admin|super_admin,admin'])->group(function () use ($redirectWithToast, $repairMediaStorage, $mediaStorageStatus) {
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

    Route::post('/storage-link', function () use ($redirectWithToast, $repairMediaStorage) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            $result = $repairMediaStorage(false);
            return $redirectWithToast('success', implode(' | ', $result['messages']));
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Media storage repair failed: '.$exception->getMessage());
        }
    })->name('storage-link');

    Route::post('/storage-link-rebuild', function () use ($redirectWithToast, $repairMediaStorage) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            $result = $repairMediaStorage(true);
            return $redirectWithToast('success', implode(' | ', $result['messages']));
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Forced media storage repair failed: '.$exception->getMessage());
        }
    })->name('storage-link-rebuild');

    Route::post('/storage-link-status', function () use ($redirectWithToast, $mediaStorageStatus) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            $status = $mediaStorageStatus();
            $message = 'Status: '.$status['status'].' | Public disk root: '.$status['configured_root'].' | Public URL: '.$status['configured_url'].' | Web storage: '.($status['web_storage'] ?: 'not detected').($status['target'] ? ' | Target: '.$status['target'] : '');
            return $redirectWithToast(in_array($status['status'], ['symlink', 'direct-public-directory'], true) ? 'success' : 'warning', $message);
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Media storage status check failed: '.$exception->getMessage());
        }
    })->name('storage-link-status');


    Route::post('/deploy/migrate', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());
            return $redirectWithToast('success', $output !== '' ? 'Production migrations completed. '.$output : 'Production migrations completed successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Production migration failed: '.$exception->getMessage());
        }
    })->name('deploy-migrate');

    Route::post('/deploy/sync-permissions', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder', '--force' => true]);
            Artisan::call('permission:cache-reset');
            return $redirectWithToast('success', 'Role permissions synchronized and permission cache reset successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Permission sync failed: '.$exception->getMessage());
        }
    })->name('deploy-sync-permissions');

    Route::post('/deploy/config-cache', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            Artisan::call('config:cache');
            return $redirectWithToast('success', 'Production configuration cache built successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'Config cache build failed: '.$exception->getMessage());
        }
    })->name('deploy-config-cache');

    Route::post('/deploy/view-cache', function () use ($redirectWithToast) {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403);

        try {
            Artisan::call('view:cache');
            return $redirectWithToast('success', 'Production Blade view cache built successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectWithToast('error', 'View cache build failed: '.$exception->getMessage());
        }
    })->name('deploy-view-cache');

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
