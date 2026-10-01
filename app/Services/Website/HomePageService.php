<?php

namespace App\Services\Website;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HomePageService
{
    /**
     * Build all data required by the public homepage.
     *
     * @return array<string, mixed>
     */
    public function getHomePageData(?string $searchTerm = null): array
    {
        $categories = Category::query()
            ->where('status', 'active')
            ->withCount([
                'products as active_products_count' => fn (Builder $query) => $query->where('status', 'active'),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(8)
            ->get();

        $featuredProducts = $this->baseProductQuery()
            ->where('featured', true)
            ->latest('id')
            ->limit(6)
            ->get();

        $latestProducts = $this->baseProductQuery()
            ->latest('id')
            ->limit(8)
            ->get();

        $bestSellers = $this->baseProductQuery()
            ->withSum([
                'orderItems as sold_quantity' => function (Builder $query) {
                    $query->whereHas('order', function (Builder $orderQuery) {
                        $orderQuery->whereIn('order_status', [
                            'confirmed',
                            'processing',
                            'shipped',
                            'delivered',
                        ]);
                    });
                },
            ], 'quantity')
            ->orderByDesc('sold_quantity')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $heroProducts = $featuredProducts
            ->concat($latestProducts)
            ->unique('id')
            ->take(3)
            ->values();

        $searchResults = collect();

        if ($searchTerm !== null && $searchTerm !== '') {
            $searchResults = $this->searchProducts($searchTerm);
        }

        return compact(
            'categories',
            'featuredProducts',
            'latestProducts',
            'bestSellers',
            'heroProducts',
            'searchResults',
        );
    }

    private function baseProductQuery(): Builder
    {
        return Product::query()
            ->where('status', 'active')
            ->with('category');
    }

    private function searchProducts(string $searchTerm): Collection
    {
        return $this->baseProductQuery()
            ->where(function (Builder $query) use ($searchTerm) {
                $query
                    ->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('sku', 'like', "%{$searchTerm}%")
                    ->orWhere('short_description', 'like', "%{$searchTerm}%");
            })
            ->latest('id')
            ->limit(8)
            ->get();
    }
}
