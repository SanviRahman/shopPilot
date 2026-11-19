<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorOrder;
use App\Services\MarketplaceOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly MarketplaceOrderService $orderService)
    {
    }

    public function index(Request $request): View
    {
        $vendor = $request->attributes->get('vendor');
        $orders = VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->with('order')
            ->withCount('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($builder) => $builder->where('suborder_number', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($orderQuery) => $orderQuery->where('order_number', 'like', "%{$search}%")));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('vendor-portal.sales.orders.index', compact('vendor', 'orders'));
    }

    public function show(Request $request, VendorOrder $vendorOrder): View
    {
        $vendor = $request->attributes->get('vendor');
        abort_unless((int) $vendorOrder->vendor_id === (int) $vendor->id, 404);

        $vendorOrder->load(['order.paymentSubmission.paymentMethod', 'items.product', 'items.variant', 'refunds.items', 'histories.admin', 'histories.user']);

        return view('vendor-portal.sales.orders.show', compact('vendor', 'vendorOrder'));
    }

    public function updateStatus(Request $request, VendorOrder $vendorOrder): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        abort_unless((int) $vendorOrder->vendor_id === (int) $vendor->id, 404);

        $validated = Validator::make($request->all(), [
            'status' => ['required', 'in:processing,ready_to_ship,shipped'],
            'shipping_provider' => ['required_if:status,shipped', 'nullable', 'string', 'max:100'],
            'tracking_number' => ['required_if:status,shipped', 'nullable', 'string', 'max:120'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
        ])->validate();

        $this->orderService->updateVendorFulfilment($vendor, $vendorOrder, $validated);

        return back()->with('success', 'Order fulfilment updated successfully.');
    }
}
