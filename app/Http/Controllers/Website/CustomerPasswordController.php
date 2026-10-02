<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\ChangeCustomerPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerPasswordController extends Controller
{
    public function edit(Request $request): View
    {
        return view('website.account.password.edit', [
            'user' => $request->user('web'),
        ]);
    }

    public function update(ChangeCustomerPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user('web');
        $user->forceFill([
            'password' => Hash::make((string) $request->validated('password')),
        ])->save();

        $request->session()->regenerate();

        $message = 'Your password has been changed successfully.';

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
