<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('products.view');

        $products = Product::onlyTrashed()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.products.partials.table', [
                    'products' => $products,
                    'isTrash' => true,
                ])->render(),
                'pagination' => $products->hasPages() ? (string) $products->links() : '',
            ]);
        }

        return view('backoffice.admin.products.trash', [
            'title' => 'Products Trash',
            'breadcrumb' => [
                ['text' => 'Products', 'url' => route('admin.products.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'products' => $products,
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse|RedirectResponse
    {
        $product = $this->productService->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'product' => $product,
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Request $request, Product $product): View|JsonResponse
    {
        $this->authorizeAction('products.view');
        $product->load('category');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'category' => $product->category?->name,
                    'regular_price' => number_format($product->regular_price, 2),
                    'sale_price' => $product->sale_price ? number_format($product->sale_price, 2) : null,
                    'stock_quantity' => $product->stock_quantity,
                    'description' => $product->description ?: 'No description provided.',
                    'status' => ucfirst($product->status),
                    'featured' => $product->featured,
                    'image' => $product->getThumbnailUrlAttribute(),
                    'created_at' => optional($product->created_at)->format('d M Y, h:i A'),
                    'seo' => [
                        'meta_title' => $product->getMetaTitle(),
                        'meta_description' => $product->getMetaDescription(),
                        'meta_keywords' => $product->meta_keywords ?: 'N/A',
                        'canonical_url' => $product->canonical_url ?: 'N/A',
                        'og_title' => $product->getOgTitle(),
                        'og_description' => $product->getOgDescription(),
                        'og_image' => $product->getOgImageUrl(),
                        'twitter_title' => $product->getTwitterTitle(),
                        'twitter_description' => $product->getTwitterDescription(),
                        'twitter_image' => $product->getTwitterImageUrl(),
                    ],
                ],
            ]);
        }

        return view('backoffice.admin.products.show', compact('product'));
    }

    public function edit(Request $request, Product $product): View|JsonResponse
    {
        $this->authorizeAction('products.update');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'category_id' => $product->category_id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'short_description' => $product->short_description,
                    'description' => $product->description,
                    'regular_price' => $product->regular_price,
                    'sale_price' => $product->sale_price,
                    'stock_quantity' => $product->stock_quantity,
                    'status' => $product->status,
                    'featured' => $product->featured,
                    'image_url' => $product->getThumbnailUrlAttribute(),
                    'image_media_id' => $product->getFirstMedia('product_thumbnail')?->id,
                    'image_media_name' => $product->getFirstMedia('product_thumbnail')?->name,
                    'meta_title' => $product->meta_title,
                    'meta_description' => $product->meta_description,
                    'meta_keywords' => $product->meta_keywords,
                    'canonical_url' => $product->canonical_url,
                    'og_title' => $product->og_title,
                    'og_description' => $product->og_description,
                    'og_image_url' => $product->getFirstMediaUrl('product_og_image'),
                    'og_image_media_id' => $product->getFirstMedia('product_og_image')?->id,
                    'og_image_media_name' => $product->getFirstMedia('product_og_image')?->name,
                    'twitter_title' => $product->twitter_title,
                    'twitter_description' => $product->twitter_description,
                    'twitter_image_url' => $product->getFirstMediaUrl('product_twitter_image'),
                    'twitter_image_media_id' => $product->getFirstMedia('product_twitter_image')?->id,
                    'twitter_image_media_name' => $product->getFirstMedia('product_twitter_image')?->name,
                ],
            ]);
        }

        return $this->indexView($request, $product, true);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        $this->productService->update($product, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('products.delete');
        $this->productService->delete($product);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product moved to trash successfully.',
            ]);
        }

        return back()->with('success', 'Product moved to trash.');
    }

    public function restore(Request $request, int $product): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('products.restore');
        $trashedProduct = Product::onlyTrashed()->findOrFail($product);
        $this->productService->restore($trashedProduct);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product restored successfully.',
            ]);
        }

        return back()->with('success', 'Product restored successfully.');
    }

    public function forceDelete(Request $request, int $product): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('products.force-delete');
        $trashedProduct = Product::onlyTrashed()->findOrFail($product);
        $this->productService->forceDelete($trashedProduct);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product permanently deleted.',
            ]);
        }

        return back()->with('success', 'Product permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'delete' => 'products.delete',
            'restore' => 'products.restore',
            'force-delete' => 'products.force-delete',
            default => null,
        };

        if (! $permission) {
            return response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422);
        }

        $this->authorizeAction($permission);

        $validated = Validator::make($request->all(), [
            'action' => ['required', 'in:delete,restore,force-delete'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer'],
        ])->validate();

        $result = $this->productService->bulk($validated['action'], $validated['product_ids']);
        $message = sprintf('%d product(s) processed.', $result['processed']);

        if ($result['skipped'] > 0) {
            $message .= sprintf(' %d skipped.', $result['skipped']);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    private function indexView(Request $request, ?Product $formProduct = null, bool $openForm = false): View|JsonResponse
    {
        $this->authorizeAction('products.view');

        $products = Product::with('category')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.products.partials.table', [
                    'products' => $products,
                    'isTrash' => false,
                ])->render(),
                'pagination' => $products->hasPages() ? (string) $products->links() : '',
            ]);
        }

        return view('backoffice.admin.products.index', [
            'title' => 'Product Management',
            'breadcrumb' => [['text' => 'Products', 'url' => null]],
            'products' => $products,
            'formProduct' => $formProduct,
            'formMode' => $formProduct ? 'edit' : 'create',
            'openForm' => $openForm,
        ]);
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}