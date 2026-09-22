<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AdminAvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct(
        private readonly AdminAvatarService $avatarService
    ) {
    }

    public function profile(Request $request)
    {
        $admin = $this->admin($request);

        $breadcrumb = [
            ['text' => 'Profile', 'url' => null],
        ];

        return view('backoffice.admin.admins.profile', [
            'title' => 'Update Profile',
            'sub_title' => 'Manage your admin account information and profile photo.',
            'breadcrumb' => $breadcrumb,
            'breadcrumbs' => $breadcrumb,
            'admin' => $admin,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $admin = $this->admin($request);

        $validated = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:191',Rule::unique('admins', 'email')->ignore($admin->id)],
            'photo' => ['nullable','image','mimes:jpeg,png,jpg,webp','max:2048'],
            'photo_media_id' => ['nullable','integer','exists:media,id'],
        ]);

        $admin->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $this->avatarService->syncFromRequest($request, $admin);

        return back()->with(
            'success',
            'Profile updated successfully.'
        );
    }

    public function password()
    {
        $breadcrumb = [
            ['text' => 'Change Password', 'url' => null],
        ];

        return view('backoffice.admin.admins.password', [
            'title' => 'Change Password',
            'sub_title' => 'Update your password to keep your admin account secure.',
            'breadcrumb' => $breadcrumb,
            'breadcrumbs' => $breadcrumb,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $admin = $this->admin($request);

        $validated = $request->validate([
            'current_password' => ['required','string'],
            'password' => ['required','confirmed',Password::defaults()],
        ]);

        if (!Hash::check(
            $validated['current_password'],
            $admin->password
        )) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $admin->update(['password' => $validated['password']]);

        return back()->with(
            'success',
            'Password updated successfully.'
        );
    }

    private function admin(Request $request): Admin
    {
        $admin = $request->user('admin');

        abort_unless($admin instanceof Admin, 401);

        return $admin;
    }
}