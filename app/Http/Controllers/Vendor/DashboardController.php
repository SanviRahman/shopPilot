<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorOrder;
use App\Services\MarketplaceLedgerService;
use App\Services\Vendor\VendorInventoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly VendorInventoryService $inventoryService,
        private readonly MarketplaceLedgerService $ledgerService,
    ) {
    }

    public function index(Request $request): View
    {
        /** @var Vendor $vendor */
        $vendor = $request->attributes->get('vendor');
        $products = $vendor->products();
        $inventory = $this->inventoryService->rows($vendor);

        return view('vendor-portal.dashboard.index', [
            'vendor' => $vendor->load('store'),
            'stats' => [
                'products_total' => (clone $products)->count(),
                'products_active' => (clone $products)->where('status', 'active')->where('moderation_status', 'approved')->count(),
                'products_pending_review' => (clone $products)->where('moderation_status', 'pending_review')->count(),
                'low_stock' => $this->inventoryService->summary($inventory)['low_stock'],
                'orders_total' => VendorOrder::query()->where('vendor_id', $vendor->id)->count(),
                'orders_to_process' => VendorOrder::query()->where('vendor_id', $vendor->id)->whereIn('status', ['confirmed', 'processing', 'ready_to_ship'])->count(),
                'available_earnings' => $this->ledgerService->balance($vendor, 'available'),
            ],
            'recentProducts' => (clone $products)->with('category')->latest('id')->limit(6)->get(),
            'recentOrders' => VendorOrder::query()->where('vendor_id', $vendor->id)->with('order')->latest()->limit(5)->get(),
        ]);
    }
}
