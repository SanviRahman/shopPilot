<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\UpdateCustomerProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('website.account.profile.edit', [
            'user' => $request->user('web'),
        ]);
    }

    public function update(UpdateCustomerProfileRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user('web');
        $data = $request->safe()->except(['avatar', 'remove_avatar']);
        $emailChanged = isset($data['email']) && $data['email'] !== $user->email;

        $user->fill($data);
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->save();

        if ($request->boolean('remove_avatar')) {
            $user->clearMediaCollection('avatar');
        }

        if ($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        $message = 'Your profile has been updated successfully.';
        $avatarUrl = $user->getFirstMediaUrl('avatar');

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'profile' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'avatar_url' => $avatarUrl ?: null,
                    'avatar_initial' => strtoupper(substr((string) $user->name, 0, 1)),
                ],
            ]);
        }

        return back()->with('success', $message);
    }
}
