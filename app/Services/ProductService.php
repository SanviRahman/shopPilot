<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function create(array $data, ?int $createdByUserId = null): Product
    {
        return DB::transaction(function () use ($data, $createdByUserId): Product {
            $productType = $data['product_type'] ?? 'simple';
            $variants = $data['variants'] ?? [];
            $productData = $this->withoutMediaFields($data);
            $productData['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name']);

            if ($productType === 'variable') {
                $productData['regular_price'] = $variants[0]['regular_price'];
                $productData['sale_price'] = null;
                $productData['stock_quantity'] = 0;
            }

            $product = Product::create($productData);
            $this->handleMediaUploads($product, $data);

            if ($productType === 'variable') {
                $this->syncVariants($product, $variants, $createdByUserId);
            }

            return $product->refresh();
        });
    }

    public function update(Product $product, array $data, ?int $createdByUserId = null): Product
    {
        return DB::transaction(function () use ($product, $data, $createdByUserId): Product {
            $productType = $data['product_type'] ?? 'simple';
            $variants = $data['variants'] ?? [];
            $productData = $this->withoutMediaFields($data);
            $productData['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name'], $product->id);

            if ($productType === 'variable') {
                $productData['regular_price'] = $variants[0]['regular_price'];
                $productData['sale_price'] = null;
                $productData['stock_quantity'] = 0;
            }

            $product->update($productData);
            $this->handleMediaUploads($product, $data);

            if ($productType === 'variable') {
                $this->syncVariants($product, $variants, $createdByUserId);
            } else {
                $product->variants()->get()->each(function (ProductVariant $variant) {
                    $variant->update(['is_active' => false]);
                    $variant->delete();
                });
            }

            return $product->refresh();
        });
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function restore(Product $product): void
    {
        $product->restore();
    }

    public function forceDelete(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            if ($product->orderItems()->withTrashed()->exists()) {
                throw ValidationException::withMessages([
                    'product' => 'This product is referenced by historical order items and cannot be permanently deleted.',
                ]);
            }

            if ($product->moderationHistories()->exists()) {
                throw ValidationException::withMessages([
                    'product' => 'This product has moderation history and cannot be permanently deleted.',
                ]);
            }

            $product->variants()->withTrashed()->get()->each->clearMediaCollection('variant_image');
            $product->clearMediaCollection('product_thumbnail');
            $product->clearMediaCollection('product_gallery');
            $product->clearMediaCollection('product_og_image');
            $product->clearMediaCollection('product_twitter_image');
            $product->forceDelete();
        });
    }

    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $product = Product::withTrashed()->find($id);

                if (! $product) {
                    $skipped++;
                    continue;
                }

                if (
                    $action === 'force-delete'
                    && $product->trashed()
                    && ($product->orderItems()->withTrashed()->exists() || $product->moderationHistories()->exists())
                ) {
                    $skipped++;
                    continue;
                }

                match ($action) {
                    'delete' => $this->delete($product),
                    'restore' => $product->trashed() ? $this->restore($product) : null,
                    'force-delete' => $product->trashed() ? $this->forceDelete($product) : null,
                    default => null,
                };
                $processed++;
            }

            return compact('processed', 'skipped');
        });
    }

    private function syncVariants(Product $product, array $variants, ?int $createdByUserId = null): void
    {
        $keepIds = [];
        $attributeSet = null;
        $combinations = [];

        foreach (array_values($variants) as $index => $data) {
            $valueIds = collect($data['attribute_value_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->sort()
                ->values();

            $values = ProductAttributeValue::query()
                ->with('attribute')
                ->whereIn('id', $valueIds)
                ->where('is_active', true)
                ->whereHas('attribute', fn ($query) => $query->where('is_active', true))
                ->get();

            if ($values->count() !== $valueIds->count()) {
                throw ValidationException::withMessages([
                    "variants.{$index}.attribute_value_ids" => 'Choose active values from the selected product attributes.',
                ]);
            }

            $attributeIds = $values->pluck('product_attribute_id')->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();

            if (count($attributeIds) !== $values->count()) {
                throw ValidationException::withMessages([
                    "variants.{$index}.attribute_value_ids" => 'Each variation must contain only one value per attribute.',
                ]);
            }

            if ($attributeSet === null) {
                $attributeSet = $attributeIds;
            } elseif ($attributeSet !== $attributeIds) {
                throw ValidationException::withMessages([
                    "variants.{$index}.attribute_value_ids" => 'Every variation must use the same set of attributes.',
                ]);
            }

            $combinationKey = $valueIds->implode('-');
            if (isset($combinations[$combinationKey])) {
                throw ValidationException::withMessages([
                    "variants.{$index}.attribute_value_ids" => 'Duplicate variation combinations are not allowed.',
                ]);
            }
            $combinations[$combinationKey] = true;

            $regularPrice = (float) $data['regular_price'];
            $salePrice = isset($data['sale_price']) && $data['sale_price'] !== '' ? (float) $data['sale_price'] : null;

            if ($salePrice !== null && $salePrice >= $regularPrice) {
                throw ValidationException::withMessages([
                    "variants.{$index}.sale_price" => 'Sale price must be lower than regular price.',
                ]);
            }

            $variantId = $data['id'] ?? null;
            $variant = $variantId
                ? $product->variants()->whereKey($variantId)->first()
                : null;

            if ($variantId && ! $variant) {
                throw ValidationException::withMessages([
                    "variants.{$index}.id" => 'The selected variation does not belong to this product.',
                ]);
            }

            $sku = trim($data['sku']);
            $this->ensureUniqueSku($sku, $product, $variant);

            $variant ??= new ProductVariant(['product_id' => $product->id]);
            $variant->fill([
                'sku' => $sku,
                'regular_price' => $regularPrice,
                'sale_price' => $salePrice,
                'is_active' => true,
            ])->save();

            $variant->attributeValues()->sync($valueIds->all());
            $this->syncInventory($variant, (int) $data['stock_quantity'], $createdByUserId);
            $this->handleVariantImage($variant, $data['image'] ?? null);
            $keepIds[] = $variant->id;
        }

        $product->variants()
            ->whereNotIn('id', $keepIds)
            ->get()
            ->each(function (ProductVariant $variant) {
                $variant->update(['is_active' => false]);
                $variant->delete();
            });

        $activeVariants = $product->variants()
            ->where('is_active', true)
            ->with('inventoryBalance')
            ->get();
        $startingVariant = $activeVariants->sortBy(fn (ProductVariant $variant) => $this->effectivePrice($variant))->first();

        $product->update([
            'regular_price' => $startingVariant?->regular_price ?? 0,
            'sale_price' => $startingVariant?->sale_price,
            'stock_quantity' => (int) $activeVariants->sum(fn (ProductVariant $variant) => $variant->available_quantity),
        ]);
    }

    private function syncInventory(ProductVariant $variant, int $quantity, ?int $createdByUserId = null): void
    {
        $balance = InventoryBalance::query()->firstOrNew(['product_variant_id' => $variant->id]);
        $isNew = ! $balance->exists;
        $before = $balance->exists ? (int) $balance->on_hand_quantity : 0;
        $reserved = $balance->exists ? (int) $balance->reserved_quantity : 0;

        if ($quantity < $reserved) {
            throw ValidationException::withMessages([
                'variants' => 'On-hand stock cannot be lower than the quantity already reserved.',
            ]);
        }

        $balance->on_hand_quantity = $quantity;
        $balance->reserved_quantity = $reserved;
        $balance->save();

        if ($before !== $quantity) {
            InventoryMovement::create([
                'inventory_balance_id' => $balance->id,
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'created_by_user_id' => $createdByUserId,
                'movement_type' => $isNew ? 'opening' : 'adjustment',
                'quantity_change' => $quantity - $before,
                'on_hand_before' => $before,
                'on_hand_after' => $quantity,
                'note' => $isNew ? 'Initial variation stock.' : 'Variation stock adjusted from product management.',
                'created_by_admin_id' => auth('admin')->id(),
            ]);
        }
    }

    private function handleVariantImage(ProductVariant $variant, mixed $image): void
    {
        if ($image instanceof UploadedFile) {
            $variant->addMedia($image)->toMediaCollection('variant_image');
        }
    }

    private function ensureUniqueSku(string $sku, Product $product, ?ProductVariant $variant): void
    {
        $productSkuExists = Product::withTrashed()
            ->where('sku', $sku)
            ->where('id', '!=', $product->id)
            ->exists();

        $variantSkuExists = ProductVariant::withTrashed()
            ->where('sku', $sku)
            ->when($variant, fn ($query) => $query->where('id', '!=', $variant->id))
            ->exists();

        if ($sku === $product->sku || $productSkuExists || $variantSkuExists) {
            throw ValidationException::withMessages([
                'variants' => "Variation SKU '{$sku}' is already in use.",
            ]);
        }
    }

    private function effectivePrice(ProductVariant $variant): float
    {
        $regular = (float) $variant->regular_price;
        $sale = $variant->sale_price !== null ? (float) $variant->sale_price : null;

        return $sale !== null && $sale > 0 && $sale < $regular ? $sale : $regular;
    }

    private function uniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: 'product';
        $candidate = $base;
        $counter = 1;

        while (Product::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $candidate = $base . '-' . $counter++;
        }

        return $candidate;
    }

    private function handleMediaUploads(Product $product, array $data): void
    {
        $mediaFields = [
            'image' => 'product_thumbnail',
            'og_image' => 'product_og_image',
            'twitter_image' => 'product_twitter_image',
        ];

        foreach ($mediaFields as $field => $collection) {
            if (($data[$field] ?? null) instanceof UploadedFile) {
                $product->addMedia($data[$field])->toMediaCollection($collection);
                continue;
            }

            $selectedMediaId = $data[$field . '_media_id'] ?? null;
            if ($selectedMediaId) {
                $source = Media::query()
                    ->whereKey($selectedMediaId)
                    ->where('mime_type', 'like', 'image/%')
                    ->firstOrFail();

                $product->addMedia($source->getPath())
                    ->usingName($source->name)
                    ->usingFileName($source->file_name)
                    ->toMediaCollection($collection);
                continue;
            }

            if (filter_var($data['remove_' . $field] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $product->clearMediaCollection($collection);
            }
        }
    }

    private function withoutMediaFields(array $data): array
    {
        return Arr::except($data, [
            'image',
            'image_media_id',
            'og_image',
            'og_image_media_id',
            'twitter_image',
            'twitter_image_media_id',
            'remove_image',
            'remove_og_image',
            'remove_twitter_image',
            'variants',
        ]);
    }
}
