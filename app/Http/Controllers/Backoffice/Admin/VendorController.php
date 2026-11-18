<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Services\VendorService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function __construct(private readonly VendorService $vendorService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorizeAction('vendors.view');

        $vendors = Vendor::query()
            ->with(['store', 'users'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('business_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('store', fn ($storeQuery) => $storeQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('backoffice.admin.vendors.index', [
            'title' => 'Vendor Management',
            'breadcrumb' => [['text' => 'Vendors', 'url' => null]],
            'vendors' => $vendors,
        ]);
    }

    public function show(Vendor $vendor): View
    {
        $this->authorizeAction('vendors.view');

        $vendor->load([
            'store',
            'users',
            'documents.uploadedBy',
            'statusHistories.changedByAdmin',
            'statusHistories.changedByUser',
        ]);

        return view('backoffice.admin.vendors.show', [
            'title' => 'Vendor Review',
            'breadcrumb' => [
                ['text' => 'Vendors', 'url' => route('admin.vendors.index')],
                ['text' => $vendor->business_name, 'url' => null],
            ],
            'vendor' => $vendor,
        ]);
    }

    public function approve(Vendor $vendor): RedirectResponse
    {
        $this->authorizeAction('vendors.approve');
        $this->vendorService->approve($vendor, auth('admin')->user());

        return back()->with('success', 'Vendor approved successfully.');
    }

    public function reject(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->authorizeAction('vendors.reject');
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->vendorService->reject($vendor, auth('admin')->user(), $validated['reason']);

        return back()->with('success', 'Vendor application rejected.');
    }

    public function suspend(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->authorizeAction('vendors.suspend');
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->vendorService->suspend($vendor, auth('admin')->user(), $validated['reason']);

        return back()->with('success', 'Vendor suspended successfully.');
    }

    public function reactivate(Vendor $vendor): RedirectResponse
    {
        $this->authorizeAction('vendors.reactivate');
        $this->vendorService->reactivate($vendor, auth('admin')->user());

        return back()->with('success', 'Vendor reactivated successfully.');
    }

    public function downloadDocument(Vendor $vendor, VendorDocument $document)
    {
        $this->authorizeAction('vendors.view');
        $document = $vendor->documents()->findOrFail($document->getKey());

        return Storage::disk($document->disk)->download($document->path, $document->original_name);
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}
