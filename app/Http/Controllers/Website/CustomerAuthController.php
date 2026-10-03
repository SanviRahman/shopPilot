<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\CustomerLoginRequest;
use App\Http\Requests\Website\CustomerRegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
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

    public function authenticate(CustomerLoginRequest $request): JsonResponse|RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'message' => 'The provided credentials do not match our records.',
                    'errors' => ['email' => ['The provided credentials do not match our records.']],
                ], 422);
            }

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'The provided credentials do not match our records.']);
        }

        $user = Auth::guard('web')->user();
        if (! $user?->isActive()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($this->wantsJson($request)) {
                return response()->json([
                    'message' => 'Your account is inactive. Please contact support.',
                    'errors' => ['email' => ['Your account is inactive. Please contact support.']],
                    'csrf_token' => csrf_token(),
                ], 422);
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is inactive. Please contact support.']);
        }

        $request->session()->regenerate();
        $redirect = redirect()->intended(route('website.account.dashboard'));
        $metaEvents = [['name' => 'LoginSuccess', 'payload' => ['method' => 'password', 'status' => 'success'], 'custom' => true]];
        if ($this->wantsJson($request)) { $request->session()->flash('meta_events', $metaEvents); return response()->json(['success' => true, 'message' => 'Welcome back to ShopPilot.', 'redirect_url' => $redirect->getTargetUrl(), 'meta_events' => $metaEvents]); }
        return $redirect->with('success', 'Welcome back to ShopPilot.')->with('meta_events', $metaEvents);
    }

    public function register(): View
    {
        return view('website.auth.register');
    }

    public function store(CustomerRegisterRequest $request): JsonResponse|RedirectResponse
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

        $metaEvents = [['name' => 'CompleteRegistration', 'payload' => ['status' => 'registered']], ['name' => 'RegistrationSuccess', 'payload' => ['status' => 'success'], 'custom' => true]];
        $redirectUrl = route('website.account.dashboard');
        if ($this->wantsJson($request)) { $request->session()->flash('meta_events', $metaEvents); return response()->json(['success' => true, 'message' => 'Your ShopPilot account is ready.', 'redirect_url' => $redirectUrl, 'meta_events' => $metaEvents], 201); }
        return redirect()->to($redirectUrl)->with('success', 'Your ShopPilot account is ready.')->with('meta_events', $metaEvents);
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'You have been logged out.',
                'redirect_url' => route('website.home'),
            ]);
        }

        return redirect()->route('website.home')->with('success', 'You have been logged out.');
    }

    public function forgotPassword(): View
    {
        return view('website.auth.forgot-password');
    }

    public function sendResetLink(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate(['email' => ['required', 'email:rfc', 'max:191']]);
        $status = Password::broker('users')->sendResetLink($request->only('email'));

        if ($this->wantsJson($request)) {
            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => __($status),
                ]);
            }

            return response()->json([
                'message' => __($status),
                'errors' => ['email' => [__($status)]],
            ], 422);
        }

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

    public function updatePassword(Request $request): JsonResponse|RedirectResponse
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

        if ($this->wantsJson($request)) {
            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => __($status),
                    'redirect_url' => route('website.login'),
                ]);
            }

            return response()->json([
                'message' => __($status),
                'errors' => ['email' => [__($status)]],
            ], 422);
        }

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('website.login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    private function wantsJson(Request $request): bool
    {
        return $request->ajax() || $request->expectsJson();
    }
}
