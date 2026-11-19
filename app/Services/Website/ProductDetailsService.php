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
            ->published()
            ->with(['category', 'media', 'variants.inventoryBalance', 'variants.attributeValues.attribute', 'variants.media'])
            ->withSum([
                'orderItems as sold_quantity' => function (Builder $query) {
                    $query->whereHas('order', function (Builder $orderQuery) {
                        $orderQuery->whereIn('order_status', self::COMPLETED_SALE_STATUSES);
                    });
                },
            ], 'quantity')
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->published()
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
                ->published()
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
            'variantOptions' => $product->product_type === 'variable' ? $this->variantOptions($product) : [],
            'relatedProducts' => $relatedProducts->values(),
        ];
    }

    private function variantOptions(Product $product): array
    {
        return $product->variants
            ->where('is_active', true)
            ->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'regular_price' => (float) $variant->regular_price,
                'sale_price' => $variant->sale_price !== null ? (float) $variant->sale_price : null,
                'stock_quantity' => $variant->available_quantity,
                'image_url' => $variant->image_url,
                'attributes' => $variant->attributeValues->map(fn ($value) => [
                    'attribute_id' => $value->product_attribute_id,
                    'attribute_name' => $value->attribute->name,
                    'display_type' => $value->attribute->display_type,
                    'value_id' => $value->id,
                    'value' => $value->label,
                    'color_code' => $value->color_code,
                ])->values()->all(),
            ])
            ->values()
            ->all();
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
