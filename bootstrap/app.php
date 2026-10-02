<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin') || $request->is('admin/*')
            ? route('admin.login')
            : route('website.login'));

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user('admin') ?: $request->user();

            if ($user?->hasAnyRole(['super_admin', 'admin'])) {
                return route('admin.dashboard');
            }

            if ($request->user('web')) {
                return route('website.account.dashboard');
            }

            return route('website.home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();