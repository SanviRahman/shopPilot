<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorProductRequest;
use App\Http\Requests\Vendor\UpdateVendorProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Vendor;
use App\Services\ProductService;
use App\Services\Vendor\VendorProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly VendorProductService $vendorProductService,
        private readonly ProductService $productService,
    ) {
    }

    public function index(Request $request): View
    {
        /** @var Vendor $vendor */
        $vendor = $request->attributes->get('vendor');
        Gate::authorize('viewAny', [Product::class, $vendor]);

        $products = $vendor->products()
            ->with(['category', 'moderationHistories'])
            ->withCount('variants')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('moderation'), fn ($query) => $query->where('moderation_status', $request->string('moderation')->toString()))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($builder) => $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%"));
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('vendor-portal.catalog.products.index', compact('vendor', 'products'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Product::class);

        return $this->formView($request->attributes->get('vendor'));
    }

    public function store(StoreVendorProductRequest $request): RedirectResponse
    {
        Gate::authorize('create', Product::class);
        $vendor = $request->attributes->get('vendor');
        $product = $this->vendorProductService->create($vendor, $request->user('web'), $request->validated());

        return redirect()->route('vendor.products.index')->with('success', "{$product->name} was saved as a draft.");
    }

    public function edit(Request $request, Product $product): View
    {
        Gate::authorize('update', $product);
        $product->load(['variants.inventoryBalance', 'variants.attributeValues.attribute', 'moderationHistories']);

        return $this->formView($request->attributes->get('vendor'), $product);
    }

    public function update(UpdateVendorProductRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);
        $vendor = $request->attributes->get('vendor');
        $updated = $this->vendorProductService->update($vendor, $request->user('web'), $product, $request->validated());

        return redirect()->route('vendor.products.index')->with('success', "{$updated->name} was updated.");
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);
        $this->productService->delete($product);

        return redirect()->route('vendor.products.index')->with('success', 'Product moved to your trash.');
    }

    public function submitForReview(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);
        $this->vendorProductService->submitForReview(
            $request->attributes->get('vendor'),
            $request->user('web'),
            $product,
        );

        return back()->with('success', 'Product submitted for admin review.');
    }

    public function publish(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);
        $this->vendorProductService->publish($request->attributes->get('vendor'), $product);

        return back()->with('success', 'Product published successfully.');
    }

    public function unpublish(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);
        $this->vendorProductService->unpublish($request->attributes->get('vendor'), $product);

        return back()->with('success', 'Product unpublished successfully.');
    }

    private function formView(Vendor $vendor, ?Product $product = null): View
    {
        $attributes = ProductAttribute::query()
            ->where('is_active', true)
            ->with(['values' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $initialVariants = $product?->variants->map(fn ($variant) => [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'regular_price' => $variant->regular_price,
            'sale_price' => $variant->sale_price,
            'stock_quantity' => $variant->inventoryBalance?->on_hand_quantity ?? 0,
            'image_url' => $variant->image_url,
            'attribute_value_ids' => $variant->attributeValues->pluck('id')->values(),
        ])->values()->all() ?? [];

        return view('vendor-portal.catalog.products.form', [
            'vendor' => $vendor,
            'product' => $product,
            'categories' => Category::getTreeSelectOptions(null, true),
            'attributes' => $attributes,
            'initialVariants' => $initialVariants,
        ]);
    }
}
