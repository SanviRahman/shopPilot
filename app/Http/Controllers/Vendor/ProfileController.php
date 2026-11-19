<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateVendorProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('vendor-portal.account.profile', [
            'vendor' => $request->attributes->get('vendor'),
        ]);
    }

    public function update(UpdateVendorProfileRequest $request): RedirectResponse
    {
        $request->user('web')->update($request->validated());

        return back()->with('success', 'Account details updated successfully.');
    }
}
