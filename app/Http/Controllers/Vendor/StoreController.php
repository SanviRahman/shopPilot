<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateVendorStoreRequest;
use App\Models\Store;
use App\Services\Vendor\VendorStoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(private readonly VendorStoreService $storeService)
    {
    }

    public function edit(Request $request): View
    {
        $vendor = $request->attributes->get('vendor')->load('store');
        abort_unless($vendor->store, 404);
        Gate::authorize('update', $vendor->store);

        return view('vendor-portal.store.profile', compact('vendor'));
    }

    public function update(UpdateVendorStoreRequest $request): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor')->load('store');
        abort_unless($vendor->store, 404);
        Gate::authorize('update', $vendor->store);
        $this->storeService->update($vendor->store, $request->validated());

        return back()->with('success', 'Store profile updated successfully.');
    }
}
