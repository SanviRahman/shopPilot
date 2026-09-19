<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('categories.view');

        $hasProductCategoryColumn = $this->hasProductCategoryRelation();

        $categories = Category::onlyTrashed()
            ->when($hasProductCategoryColumn, fn ($q) => $q->withCount('products'))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.categories.partials.table', [
                    'categories' => $categories,
                    'isTrash' => true,
                ])->render(),
                'pagination' => $categories->hasPages() ? (string) $categories->links() : '',
            ]);
        }

        return view('backoffice.admin.categories.trash', [
            'title' => 'Categories Trash',
            'breadcrumb' => [
                ['text' => 'Categories', 'url' => route('admin.categories.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'categories' => $categories,
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse|RedirectResponse
    {
        $category = $this->categoryService->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category,
            ]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function show(Request $request, Category $category): View|JsonResponse
    {
        $this->authorizeAction('categories.view');

        $productsCount = $this->hasProductCategoryRelation() ? $category->products()->count() : 0;

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description ?: 'No description provided.',
                    'status' => ucfirst($category->status),
                    'sort_order' => $category->sort_order,
                    'image' => $category->getImageUrlAttribute(),
                    'products_count' => $productsCount,
                    'created_at' => optional($category->created_at)->format('d M Y, h:i A'),
                    // SEO Information
                    'seo' => [
                        'meta_title' => $category->getMetaTitle(),
                        'meta_description' => $category->getMetaDescription(),
                        'meta_keywords' => $category->meta_keywords ?: 'N/A',
                        'canonical_url' => $category->canonical_url ?: 'N/A',
                        'og_title' => $category->getOgTitle(),
                        'og_description' => $category->getOgDescription(),
                        'og_image' => $category->getOgImageUrl(),
                        'twitter_title' => $category->getTwitterTitle(),
                        'twitter_description' => $category->getTwitterDescription(),
                        'twitter_image' => $category->getTwitterImageUrl(),
                    ],
                ],
            ]);
        }

        return view('backoffice.admin.categories.show', compact('category'));
    }

    public function edit(Request $request, Category $category): View|JsonResponse
    {
        $this->authorizeAction('categories.update');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'status' => $category->status,
                    'sort_order' => $category->sort_order,
                    'image_url' => $category->getImageUrlAttribute(),
                    'meta_title' => $category->meta_title,
                    'meta_description' => $category->meta_description,
                    'meta_keywords' => $category->meta_keywords,
                    'canonical_url' => $category->canonical_url,
                    'og_title' => $category->og_title,
                    'og_description' => $category->og_description,
                    'og_image_url' => $category->getFirstMediaUrl('category_og_image'),
                    'twitter_title' => $category->twitter_title,
                    'twitter_description' => $category->twitter_description,
                    'twitter_image_url' => $category->getFirstMediaUrl('category_twitter_image'),
                ],
            ]);
        }

        return $this->indexView($request, $category, true);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse|RedirectResponse
    {
        $this->categoryService->update($category, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
            ]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('categories.delete');
        $this->categoryService->delete($category);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category moved to trash successfully.',
            ]);
        }

        return back()->with('success', 'Category moved to trash.');
    }

    public function restore(Request $request, int $category): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('categories.restore');
        $trashedCategory = Category::onlyTrashed()->findOrFail($category);
        $this->categoryService->restore($trashedCategory);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category restored successfully.',
            ]);
        }

        return back()->with('success', 'Category restored successfully.');
    }

    public function forceDelete(Request $request, int $category): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('categories.force-delete');
        $trashedCategory = Category::onlyTrashed()->findOrFail($category);
        $this->categoryService->forceDelete($trashedCategory);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category permanently deleted.',
            ]);
        }

        return back()->with('success', 'Category permanently deleted.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $this->authorizeAction('categories.update');

        $validated = Validator::make($request->all(), [
            'orders' => ['required', 'array'],
            'orders.*.id' => ['required', 'integer', 'exists:categories,id'],
            'orders.*.sort_order' => ['required', 'integer', 'min:0'],
        ])->validate();

        $this->categoryService->reorder($validated['orders']);

        return response()->json([
            'success' => true,
            'message' => 'Category sort orders updated successfully.',
        ]);
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'delete' => 'categories.delete',
            'restore' => 'categories.restore',
            'force-delete' => 'categories.force-delete',
            default => null,
        };

        if (! $permission) {
            return response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422);
        }

        $this->authorizeAction($permission);

        $validated = Validator::make($request->all(), [
            'action' => ['required', 'in:delete,restore,force-delete'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer'],
        ])->validate();

        $result = $this->categoryService->bulk($validated['action'], $validated['category_ids']);
        $message = sprintf('%d category(ies) processed.', $result['processed']);

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

    private function indexView(Request $request, ?Category $formCategory = null, bool $openForm = false): View|JsonResponse
    {
        $this->authorizeAction('categories.view');

        $hasProductCategoryColumn = $this->hasProductCategoryRelation();

        $categories = Category::query()
            ->when($hasProductCategoryColumn, fn ($q) => $q->withCount('products'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
            })
            ->orderBy('sort_order', 'asc')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.categories.partials.table', [
                    'categories' => $categories,
                    'isTrash' => false,
                ])->render(),
                'pagination' => $categories->hasPages() ? (string) $categories->links() : '',
            ]);
        }

        return view('backoffice.admin.categories.index', [
            'title' => 'Category Management',
            'breadcrumb' => [['text' => 'Categories', 'url' => null]],
            'categories' => $categories,
            'formCategory' => $formCategory,
            'formMode' => $formCategory ? 'edit' : 'create',
            'openForm' => $openForm,
        ]);
    }

    private function hasProductCategoryRelation(): bool
    {
        return Schema::hasTable('products') && Schema::hasColumn('products', 'category_id');
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}