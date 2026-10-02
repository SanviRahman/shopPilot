<?php

namespace App\Services\Website;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ShopPageService
{
    private const SORTS = [
        'featured',
        'newest',
        'price_low',
        'price_high',
        'name_az',
        'name_za',
        'best_selling',
    ];

    private const PER_PAGE_OPTIONS = [12, 24, 36];

    /**
     * @return array<string, mixed>
     */
    public function getShopPageData(Request $request): array
    {
        $categories = Category::query()
            ->where('status', 'active')
            ->with('media')
            ->withCount([
                'products as active_products_count' => fn (Builder $query) => $query->where('status', 'active'),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $rawCategories = $request->input('category', []);
        $rawCategories = is_array($rawCategories) ? $rawCategories : [$rawCategories];

        $selectedCategorySlugs = collect($rawCategories)
            ->filter(fn ($slug) => is_string($slug) && $slug !== '')
            ->map(fn ($slug) => trim($slug))
            ->unique()
            ->values();

        $availableCategorySlugs = $categories->pluck('slug');
        $selectedCategorySlugs = $selectedCategorySlugs
            ->filter(fn ($slug) => $availableCategorySlugs->contains($slug))
            ->values();

        $searchTerm = $request->string('q')->trim()->toString();
        $availability = collect($request->input('availability', []))
            ->filter(fn ($value) => in_array($value, ['in_stock', 'out_of_stock'], true))
            ->unique()
            ->values();
        $featuredOnly = $request->boolean('featured');

        $globalMaxPrice = (float) (Product::query()
            ->where('status', 'active')
            ->selectRaw('MAX(COALESCE(NULLIF(sale_price, 0), regular_price)) as max_price')
            ->value('max_price') ?? 0);
        $globalMaxPrice = max(1000, (int) (ceil($globalMaxPrice / 500) * 500));

        $minPrice = max(0, (float) $request->input('min_price', 0));
        $maxPrice = min($globalMaxPrice, max($minPrice, (float) $request->input('max_price', $globalMaxPrice)));

        $sort = $request->string('sort')->toString();
        if (! in_array($sort, self::SORTS, true)) {
            $sort = 'featured';
        }

        $perPage = (int) $request->input('per_page', 12);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 12;
        }

        $effectivePriceSql = 'COALESCE(NULLIF(sale_price, 0), regular_price)';

        $query = Product::query()
            ->where('status', 'active')
            ->with(['category', 'media']);

        if ($searchTerm !== '') {
            $query->where(function (Builder $builder) use ($searchTerm) {
                $builder
                    ->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('sku', 'like', "%{$searchTerm}%")
                    ->orWhere('short_description', 'like', "%{$searchTerm}%")
                    ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', "%{$searchTerm}%"));
            });
        }

        if ($selectedCategorySlugs->isNotEmpty()) {
            $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->whereIn('slug', $selectedCategorySlugs));
        }

        $query
            ->whereRaw("{$effectivePriceSql} >= ?", [$minPrice])
            ->whereRaw("{$effectivePriceSql} <= ?", [$maxPrice]);

        if ($availability->contains('in_stock') && ! $availability->contains('out_of_stock')) {
            $query->where('stock_quantity', '>', 0);
        } elseif ($availability->contains('out_of_stock') && ! $availability->contains('in_stock')) {
            $query->where('stock_quantity', '<=', 0);
        }

        if ($featuredOnly) {
            $query->where('featured', true);
        }

        if ($sort === 'best_selling') {
            $query
                ->withSum([
                    'orderItems as sold_quantity' => function (Builder $orderItemQuery) {
                        $orderItemQuery->whereHas('order', function (Builder $orderQuery) {
                            $orderQuery->whereIn('order_status', ['confirmed', 'processing', 'shipped', 'delivered']);
                        });
                    },
                ], 'quantity')
                ->orderByDesc('sold_quantity')
                ->orderByDesc('id');
        } else {
            match ($sort) {
                'newest' => $query->latest('id'),
                'price_low' => $query->orderByRaw("{$effectivePriceSql} ASC")->orderBy('id'),
                'price_high' => $query->orderByRaw("{$effectivePriceSql} DESC")->orderByDesc('id'),
                'name_az' => $query->orderBy('name'),
                'name_za' => $query->orderByDesc('name'),
                default => $query->orderByDesc('featured')->latest('id'),
            };
        }

        $products = $query
            ->paginate($perPage)
            ->withQueryString();

        $selectedCategories = $categories
            ->whereIn('slug', $selectedCategorySlugs)
            ->values();

        $activeFilterCount = $selectedCategorySlugs->count()
            + $availability->count()
            + ($featuredOnly ? 1 : 0)
            + ($minPrice > 0 || $maxPrice < $globalMaxPrice ? 1 : 0)
            + ($searchTerm !== '' ? 1 : 0);

        return [
            'categories' => $categories,
            'products' => $products,
            'selectedCategorySlugs' => $selectedCategorySlugs,
            'selectedCategories' => $selectedCategories,
            'searchTerm' => $searchTerm,
            'availability' => $availability,
            'featuredOnly' => $featuredOnly,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'globalMaxPrice' => $globalMaxPrice,
            'sort' => $sort,
            'perPage' => $perPage,
            'activeFilterCount' => $activeFilterCount,
        ];
    }
}
