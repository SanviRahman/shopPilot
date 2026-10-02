<?php

namespace App\Services\Website;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductDetailsService
{
    private const COMPLETED_SALE_STATUSES = [
        'confirmed',
        'processing',
        'shipped',
        'delivered',
    ];

    /**
     * @return array<string, mixed>
     */
    public function getProductDetailsData(string $slug): array
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->with(['category', 'media'])
            ->withSum([
                'orderItems as sold_quantity' => function (Builder $query) {
                    $query->whereHas('order', function (Builder $orderQuery) {
                        $orderQuery->whereIn('order_status', self::COMPLETED_SALE_STATUSES);
                    });
                },
            ], 'quantity')
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->where('status', 'active')
            ->where('id', '!=', $product->getKey())
            ->when(
                $product->category_id,
                fn (Builder $query) => $query->where('category_id', $product->category_id),
            )
            ->with(['category', 'media'])
            ->orderByDesc('featured')
            ->latest('id')
            ->limit(8)
            ->get();

        if ($relatedProducts->count() < 6) {
            $fallbackIds = $relatedProducts->pluck('id')->push($product->id);

            $fallbackProducts = Product::query()
                ->where('status', 'active')
                ->whereNotIn('id', $fallbackIds)
                ->with(['category', 'media'])
                ->orderByDesc('featured')
                ->latest('id')
                ->limit(8 - $relatedProducts->count())
                ->get();

            $relatedProducts = $relatedProducts->concat($fallbackProducts);
        }

        return [
            'product' => $product,
            'galleryImages' => $this->galleryImages($product),
            'relatedProducts' => $relatedProducts->values(),
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function galleryImages(Product $product): Collection
    {
        return collect([$product->thumbnail_url])
            ->merge($product->getMedia('product_gallery')->map(fn ($media) => $media->getUrl()))
            ->filter(fn ($url) => is_string($url) && $url !== '')
            ->unique()
            ->values();
    }
}
