<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorApplicationRequest;
use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorApplicationController extends Controller
{
    public function __construct(private readonly VendorService $vendorService)
    {
    }

    public function create(Request $request): View|RedirectResponse
    {
        $vendor = $request->user('web')->vendors()->latest('vendors.created_at')->first();

        if ($vendor) {
            return redirect()->route('website.account.seller.application');
        }

        return view('website.account.seller-application.create', [
            'user' => $request->user('web'),
        ]);
    }

    public function store(StoreVendorApplicationRequest $request): RedirectResponse
    {
        $this->vendorService->submitApplication(
            $request->user('web'),
            $request->validated(),
        );

        return redirect()
            ->route('website.account.seller.application')
            ->with('success', 'Your seller application has been submitted for review.');
    }

    public function show(Request $request): View
    {
        $vendor = Vendor::query()
            ->whereHas('users', fn ($query) => $query->where('users.id', $request->user('web')->getKey()))
            ->with(['store', 'documents', 'statusHistories'])
            ->latest('id')
            ->firstOrFail();

        return view('website.account.seller-application.show', [
            'user' => $request->user('web'),
            'vendor' => $vendor,
        ]);
    }
}
