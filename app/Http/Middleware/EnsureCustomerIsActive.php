<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerIsActive
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user('web');

        if ($user && ! $user->isActive()) {
            auth('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('website.login')->withErrors([
                'email' => 'Your account is inactive. Please contact support.',
            ]);
        }

        return $next($request);
    }
}
