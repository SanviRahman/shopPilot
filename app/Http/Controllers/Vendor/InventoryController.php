<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateVendorInventoryRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use App\Services\Vendor\VendorInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private readonly VendorInventoryService $inventoryService)
    {
    }

    public function index(Request $request): View
    {
        /** @var Vendor $vendor */
        $vendor = $request->attributes->get('vendor');
        $rows = $this->inventoryService->rows($vendor, $request->string('search')->trim()->toString() ?: null);

        return view('vendor-portal.catalog.inventory.index', [
            'vendor' => $vendor,
            'inventoryRows' => $rows,
            'stats' => $this->inventoryService->summary($rows),
        ]);
    }

    public function updateProductStock(UpdateVendorInventoryRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);
        $this->inventoryService->adjustProduct($product, (int) $request->validated('quantity'), $request->user('web')->id);

        return back()->with('success', 'Product stock updated.');
    }

    public function updateVariantStock(UpdateVendorInventoryRequest $request, ProductVariant $variant): RedirectResponse
    {
        Gate::authorize('update', $variant->product()->firstOrFail());
        $this->inventoryService->adjustVariant(
            $variant,
            (int) $request->validated('quantity'),
            $request->user('web')->id,
            $request->attributes->get('vendor'),
        );

        return back()->with('success', 'Variation stock updated.');
    }
}
