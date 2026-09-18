<?php

namespace App\Http\Controllers\Backoffice\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('adminlte::auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $guard = Auth::guard('admin');

        if (! $guard->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();
        $admin = $guard->user();

        if (! $admin instanceof Admin || ! $admin->isActive()) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'This admin account is inactive or invalid.',
            ]);
        }

        return redirect()->route('admin.redirect');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function redirect(Request $request): RedirectResponse
    {
        $admin = $request->user('admin');

        if (! $admin instanceof Admin || ! $admin->isActive()) {
            Auth::guard('admin')->logout();

            return redirect()->route('admin.login');
        }

        return match (true) {
            $admin->hasAnyRole(['super_admin', 'admin']) => redirect()->route('admin.dashboard'),
            $admin->hasRole('manager') => redirect()->route('manager.dashboard'),
            $admin->hasRole('agent') => redirect()->route('agent.dashboard'),
            default => redirect()->route('admin.login'),
        };
    }
}
