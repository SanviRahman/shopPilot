<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    private const CART_SESSION_ROOT = 'shoppilot_cart';
    private const ITEMS_SESSION_KEY = 'shoppilot_cart.items';
    private const COUPON_SESSION_KEY = 'shoppilot_cart.coupon_code';
    private const LEGACY_SESSION_KEY = 'cart';
    private const LEGACY_ITEMS_SESSION_KEY = 'cart.items';
    private const LEGACY_COUPON_SESSION_KEY = 'cart.coupon_code';

    public function __construct(private readonly CouponService $couponService)
    {
    }

    public function getCartPageData(): array
    {
        $summary = $this->summary();

        return [
            ...$summary,
            'recommendedProducts' => $this->recommendedProducts($summary['items']),
        ];
    }

    public function summary(): array
    {
        $rawItems = $this->rawItems();
        $productIds = collect($rawItems)->pluck('product_id')->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($productIds->isEmpty()) {
            $this->forgetCartIfEmpty();

            return $this->emptySummary();
        }

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->published()
            ->with(['category', 'media', 'variants.inventoryBalance', 'variants.attributeValues.attribute', 'variants.media'])
            ->get()
            ->keyBy('id');

        $normalizedItems = [];
        $cartItems = collect();
        $sessionChanged = false;

        foreach ($rawItems as $itemKey => $storedItem) {
            $productId = (int) $storedItem['product_id'];
            $product = $products->get($productId);
            $variantId = isset($storedItem['variant_id']) ? (int) $storedItem['variant_id'] : null;

            if (! $product) {
                $sessionChanged = true;
                continue;
            }

            $variant = $variantId ? $product->variants->firstWhere('id', $variantId) : null;
            if (($product->product_type === 'variable' && ! $variant) || ($variantId && (! $variant || ! $variant->is_active))) {
                $sessionChanged = true;
                continue;
            }

            $stock = $variant
                ? (int) ($variant->inventoryBalance?->available_quantity ?? 0)
                : max(0, (int) $product->stock_quantity);
            $quantity = max(1, (int) $storedItem['quantity']);

            if ($stock > 0 && $quantity > $stock) {
                $quantity = $stock;
                $sessionChanged = true;
            }

            $normalizedItems[$itemKey] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ];

            $regularPrice = (float) ($variant?->regular_price ?? $product->regular_price);
            $salePrice = $variant
                ? ($variant->sale_price !== null ? (float) $variant->sale_price : null)
                : ($product->sale_price !== null ? (float) $product->sale_price : null);
            $hasSale = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
            $unitPrice = $hasSale ? $salePrice : $regularPrice;
            $lineTotal = round($unitPrice * $quantity, 2);
            $discountPercent = $hasSale && $regularPrice > 0
                ? (int) round((($regularPrice - $salePrice) / $regularPrice) * 100)
                : 0;
            $variantLabel = $variant?->attributeValues
                ->map(fn ($value) => $value->attribute->name . ': ' . $value->label)
                ->implode(' / ');

            $cartItems->push([
                'key' => $itemKey,
                'product' => $product,
                'variant' => $variant,
                'variant_label' => $variantLabel,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'sku' => $variant?->sku ?? $product->sku,
                'image_url' => $variant?->image_url ?: $product->thumbnail_url,
                'quantity' => $quantity,
                'stock_quantity' => $stock,
                'in_stock' => $stock > 0 && $quantity <= $stock,
                'regular_price' => $regularPrice,
                'unit_price' => $unitPrice,
                'has_sale' => $hasSale,
                'discount_percent' => $discountPercent,
                'line_total' => $lineTotal,
            ]);
        }

        if ($sessionChanged || count($normalizedItems) !== count($rawItems)) {
            $this->writeItems($normalizedItems);
        }

        if ($cartItems->isEmpty()) {
            $this->forgetCartIfEmpty();

            return $this->emptySummary();
        }

        $subtotal = round((float) $cartItems->sum('line_total'), 2);
        [$coupon, $discount, $couponInvalidMessage] = $this->resolveCoupon($subtotal);
        $grandTotal = round(max(0, $subtotal - $discount), 2);
        $count = (int) $cartItems->sum('quantity');
        $hasUnavailableItems = $cartItems->contains(fn (array $item) => ! $item['in_stock']);

        return [
            'items' => $cartItems,
            'count' => $count,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'couponInvalidMessage' => $couponInvalidMessage,
            'shipping' => null,
            'shippingLabel' => 'Calculated at checkout',
            'grandTotal' => $grandTotal,
            'hasUnavailableItems' => $hasUnavailableItems,
            'canCheckout' => $cartItems->isNotEmpty() && ! $hasUnavailableItems,
        ];
    }

    public function checkoutItems(): array
    {
        return array_values($this->rawItems());
    }

    public function appliedCouponCode(): ?string
    {
        $code = session()->get(self::COUPON_SESSION_KEY, session()->get(self::LEGACY_COUPON_SESSION_KEY));

        return is_string($code) && trim($code) !== '' ? strtoupper(trim($code)) : null;
    }

    public function add(int $productId, int $quantity = 1, ?int $variantId = null): void
    {
        $product = $this->activeProduct($productId);
        $variant = $this->resolveVariant($product, $variantId);
        $stock = $variant ? $variant->available_quantity : (int) $product->stock_quantity;
        $itemKey = $variant ? 'v-' . $variant->id : 'p-' . $productId;
        $quantity = max(1, $quantity);
        $items = $this->rawItems();
        $newQuantity = (int) ($items[$itemKey]['quantity'] ?? 0) + $quantity;

        if ($stock <= 0 || $newQuantity > $stock) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$stock} unit(s) of {$product->name} are currently available for this option.",
            ]);
        }

        $items[$itemKey] = [
            'product_id' => $productId,
            'variant_id' => $variant?->id,
            'quantity' => $newQuantity,
        ];

        $this->writeItems($items);
    }

    public function update(string $itemKey, int $quantity): void
    {
        $items = $this->rawItems();
        $storedItem = $items[$itemKey] ?? null;

        if (! $storedItem) {
            throw ValidationException::withMessages(['cart' => 'That item is no longer in your cart.']);
        }

        $product = $this->activeProduct((int) $storedItem['product_id']);
        $variant = $this->resolveVariant($product, $storedItem['variant_id'] ?? null);
        $stock = $variant ? $variant->available_quantity : (int) $product->stock_quantity;

        if ($stock <= 0 || $quantity > $stock) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$stock} unit(s) of {$product->name} are currently available for this option.",
            ]);
        }

        $items[$itemKey]['quantity'] = max(1, $quantity);
        $this->writeItems($items);
    }

    public function remove(string $itemKey): void
    {
        $items = $this->rawItems();
        unset($items[$itemKey]);
        $this->writeItems($items);
        $this->forgetCartIfEmpty();
    }

    public function removeMany(array $itemKeys): int
    {
        $items = $this->rawItems();
        $removed = 0;

        foreach (array_unique(array_map('strval', $itemKeys)) as $itemKey) {
            if (ctype_digit($itemKey)) {
                $itemKey = 'p-' . $itemKey;
            }

            if (! isset($items[$itemKey])) {
                continue;
            }

            unset($items[$itemKey]);
            $removed++;
        }

        $this->writeItems($items);
        $this->forgetCartIfEmpty();

        return $removed;
    }

    public function clear(): void
    {
        session()->forget([self::CART_SESSION_ROOT, self::LEGACY_SESSION_KEY]);
    }

    public function applyCoupon(string $code): void
    {
        $summary = $this->summary();

        if ($summary['items']->isEmpty()) {
            throw ValidationException::withMessages(['coupon_code' => 'Add at least one product before applying a coupon.']);
        }

        $coupon = $this->couponService->resolveApplicableCoupon($code, (float) $summary['subtotal']);
        session()->put(self::COUPON_SESSION_KEY, $coupon->code);
    }

    public function removeCoupon(): void
    {
        session()->forget([self::COUPON_SESSION_KEY, self::LEGACY_COUPON_SESSION_KEY]);
    }

    private function activeProduct(int $productId): Product
    {
        $product = Product::query()->published()->whereKey($productId)->first();

        if (! $product) {
            throw ValidationException::withMessages(['product' => 'This product is not available for purchase.']);
        }

        return $product;
    }

    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        if ($product->product_type !== 'variable') {
            if ($variantId) {
                throw ValidationException::withMessages(['variant_id' => 'This product does not have selectable variations.']);
            }

            return null;
        }

        $variant = $variantId
            ? ProductVariant::query()
                ->whereKey($variantId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->with('inventoryBalance')
                ->first()
            : null;

        if (! $variant || ! $variant->inventoryBalance) {
            throw ValidationException::withMessages(['variant_id' => 'Please select an available product variation.']);
        }

        return $variant;
    }

    private function rawItems(): array
    {
        $items = session()->get(self::ITEMS_SESSION_KEY);

        if (! is_array($items)) {
            $legacyItems = session()->get(self::LEGACY_ITEMS_SESSION_KEY);
            if (is_array($legacyItems)) {
                $items = $legacyItems;
            } else {
                $legacy = session()->get(self::LEGACY_SESSION_KEY, []);
                $items = is_array($legacy) && isset($legacy['items']) && is_array($legacy['items'])
                    ? $legacy['items']
                    : (is_array($legacy) ? $legacy : []);
            }
        }

        $normalized = [];
        foreach ($items as $key => $item) {
            $productId = is_array($item)
                ? (int) ($item['product_id'] ?? $item['id'] ?? $key)
                : (int) $key;
            $variantId = is_array($item) && ! empty($item['variant_id']) ? (int) $item['variant_id'] : null;
            $quantity = is_array($item) ? max(1, (int) ($item['quantity'] ?? 1)) : max(1, (int) $item);

            if ($productId <= 0) {
                continue;
            }

            $itemKey = $variantId ? 'v-' . $variantId : 'p-' . $productId;
            $normalized[$itemKey] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ];
        }

        return $normalized;
    }

    private function writeItems(array $items): void
    {
        session()->put(self::ITEMS_SESSION_KEY, $items);
        session()->forget(self::LEGACY_SESSION_KEY);
    }

    private function resolveCoupon(float $subtotal): array
    {
        $code = $this->appliedCouponCode();

        if ($code === null || $subtotal <= 0) {
            return [null, 0.0, null];
        }

        try {
            $coupon = $this->couponService->resolveApplicableCoupon($code, $subtotal);
            session()->put(self::COUPON_SESSION_KEY, $coupon->code);

            return [$coupon, $this->couponService->calculateDiscount($coupon, $subtotal), null];
        } catch (ValidationException $exception) {
            $this->removeCoupon();
            $messages = $exception->errors()['coupon_code'] ?? [];

            return [null, 0.0, $messages[0] ?? 'The applied coupon is no longer valid.'];
        }
    }

    private function recommendedProducts(Collection $items): Collection
    {
        $excludedIds = $items->pluck('product_id')->map(fn ($id) => (int) $id)->all();
        $categoryIds = $items->map(fn (array $item) => $item['product']->category_id)->filter()->unique()->values();
        $baseQuery = Product::query()
            ->published()
            ->where('stock_quantity', '>', 0)
            ->when($excludedIds !== [], fn (Builder $query) => $query->whereNotIn('id', $excludedIds))
            ->with(['category', 'media']);

        $recommended = (clone $baseQuery)
            ->when($categoryIds->isNotEmpty(), fn (Builder $query) => $query->whereIn('category_id', $categoryIds))
            ->orderByDesc('featured')
            ->latest('id')
            ->limit(8)
            ->get();

        if ($recommended->count() < 4) {
            $fallbackExcluded = array_merge($excludedIds, $recommended->pluck('id')->all());
            $fallback = Product::query()
                ->published()
                ->where('stock_quantity', '>', 0)
                ->when($fallbackExcluded !== [], fn (Builder $query) => $query->whereNotIn('id', $fallbackExcluded))
                ->with(['category', 'media'])
                ->orderByDesc('featured')
                ->latest('id')
                ->limit(8 - $recommended->count())
                ->get();
            $recommended = $recommended->concat($fallback);
        }

        return $recommended->values();
    }

    private function forgetCartIfEmpty(): void
    {
        if ($this->rawItems() === []) {
            $this->clear();
        }
    }

    private function emptySummary(): array
    {
        return [
            'items' => collect(),
            'count' => 0,
            'subtotal' => 0.0,
            'coupon' => null,
            'discount' => 0.0,
            'couponInvalidMessage' => null,
            'shipping' => null,
            'shippingLabel' => 'Calculated at checkout',
            'grandTotal' => 0.0,
            'hasUnavailableItems' => false,
            'canCheckout' => false,
        ];
    }
}
