<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\CustomerLoginRequest;
use App\Http\Requests\Website\CustomerRegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function login(): View
    {
        return view('website.auth.login');
    }

    public function authenticate(CustomerLoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'The provided credentials do not match our records.']);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('website.account.dashboard'))
            ->with('success', 'Welcome back to ShopPilot.');
    }

    public function register(): View
    {
        return view('website.auth.register');
    }

    public function store(CustomerRegisterRequest $request): RedirectResponse
    {
        $user = User::create($request->safe()->only(['name', 'email', 'password']));

        $customerRole = Role::query()
            ->where('name', 'customer')
            ->where('guard_name', 'web')
            ->first();

        if ($customerRole) {
            $user->assignRole($customerRole);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('website.account.dashboard')
            ->with('success', 'Your ShopPilot account is ready.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('website.home')->with('success', 'You have been logged out.');
    }

    public function forgotPassword(): View
    {
        return view('website.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email:rfc', 'max:191']]);
        $status = Password::broker('users')->sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetPassword(Request $request, string $token): View
    {
        return view('website.auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email:rfc', 'max:191'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('website.login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
