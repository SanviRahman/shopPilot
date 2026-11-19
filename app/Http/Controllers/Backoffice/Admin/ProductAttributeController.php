<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductAttribute\SaveProductAttributeRequest;
use App\Models\ProductAttribute;
use App\Services\ProductAttributeService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductAttributeController extends Controller
{
    public function __construct(private readonly ProductAttributeService $attributeService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorizeAction('product-attributes.view');

        $attributes = ProductAttribute::query()
            ->withCount('values')
            ->with('values')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $editingAttribute = $request->filled('edit')
            ? ProductAttribute::with('values')->findOrFail($request->integer('edit'))
            : null;

        return view('backoffice.admin.product-attributes.index', compact('attributes', 'editingAttribute') + [
            'title' => 'Product Attributes',
            'breadcrumb' => [['text' => 'Product Attributes', 'url' => null]],
        ]);
    }

    public function modal(): JsonResponse
    {
        $this->authorizeAction('product-attributes.view');

        $attributes = ProductAttribute::query()
            ->withCount('values')
            ->with('values')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $productAttributes = ProductAttribute::query()
            ->where('is_active', true)
            ->with(['values' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'html' => view('backoffice.admin.product-attributes.partials.modal-content', compact('attributes'))->render(),
            'productAttributes' => $productAttributes->map(fn ($attribute) => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'display_type' => $attribute->display_type,
                'values' => $attribute->values->map(fn ($value) => [
                    'id' => $value->id,
                    'label' => $value->label,
                    'slug' => $value->slug,
                    'color_code' => $value->color_code,
                ])->values(),
            ])->values(),
        ]);
    }

    public function store(SaveProductAttributeRequest $request): JsonResponse|RedirectResponse
    {
        $attribute = $this->attributeService->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Attribute created successfully.', 'attribute' => $attribute]);
        }

        return redirect()->route('admin.product-attributes.index')->with('success', 'Attribute created successfully.');
    }

    public function update(SaveProductAttributeRequest $request, ProductAttribute $productAttribute): JsonResponse|RedirectResponse
    {
        $attribute = $this->attributeService->update($productAttribute, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Attribute updated successfully.', 'attribute' => $attribute]);
        }

        return redirect()->route('admin.product-attributes.index')->with('success', 'Attribute updated successfully.');
    }

    public function destroy(Request $request, ProductAttribute $productAttribute): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('product-attributes.delete');
        $this->attributeService->delete($productAttribute);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Attribute deleted successfully.']);
        }

        return redirect()->route('admin.product-attributes.index')->with('success', 'Attribute deleted successfully.');
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}
