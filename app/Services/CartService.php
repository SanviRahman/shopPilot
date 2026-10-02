<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    private const ITEMS_SESSION_KEY = 'cart.items';
    private const LEGACY_SESSION_KEY = 'cart';
    private const COUPON_SESSION_KEY = 'cart.coupon_code';

    public function __construct(private readonly CouponService $couponService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getCartPageData(): array
    {
        $summary = $this->summary();

        return [
            ...$summary,
            'recommendedProducts' => $this->recommendedProducts($summary['items']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $rawItems = $this->rawItems();
        $productIds = collect($rawItems)
            ->map(fn (array $item) => (int) ($item['product_id'] ?? 0))
            ->filter()
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            $this->forgetCartIfEmpty();

            return $this->emptySummary();
        }

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('status', 'active')
            ->with(['category', 'media'])
            ->get()
            ->keyBy('id');

        $normalizedItems = [];
        $cartItems = collect();
        $sessionChanged = false;

        foreach ($rawItems as $key => $storedItem) {
            $productId = (int) ($storedItem['product_id'] ?? $key);
            $product = $products->get($productId);

            if (! $product) {
                $sessionChanged = true;
                continue;
            }

            $quantity = max(1, (int) ($storedItem['quantity'] ?? 1));
            $stock = max(0, (int) $product->stock_quantity);

            if ($stock > 0 && $quantity > $stock) {
                $quantity = $stock;
                $sessionChanged = true;
            }

            $normalizedItems[$productId] = [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];

            $regularPrice = (float) $product->regular_price;
            $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;
            $hasSale = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
            $unitPrice = $hasSale ? $salePrice : $regularPrice;
            $lineTotal = round($unitPrice * $quantity, 2);
            $discountPercent = $hasSale && $regularPrice > 0
                ? (int) round((($regularPrice - $salePrice) / $regularPrice) * 100)
                : 0;

            $cartItems->push([
                'product' => $product,
                'product_id' => $productId,
                'quantity' => $quantity,
                'stock_quantity' => $stock,
                'in_stock' => $stock > 0,
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

    public function add(int $productId, int $quantity = 1): void
    {
        $product = $this->activeProduct($productId);
        $stock = (int) $product->stock_quantity;

        if ($stock <= 0) {
            throw ValidationException::withMessages([
                'product' => 'This product is currently out of stock.',
            ]);
        }

        $quantity = max(1, $quantity);
        $items = $this->rawItems();
        $existingQuantity = (int) ($items[$productId]['quantity'] ?? 0);
        $newQuantity = $existingQuantity + $quantity;

        if ($newQuantity > $stock) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$stock} unit(s) of {$product->name} are currently available.",
            ]);
        }

        $items[$productId] = [
            'product_id' => $productId,
            'quantity' => $newQuantity,
        ];

        $this->writeItems($items);
    }

    public function update(int $productId, int $quantity): void
    {
        $items = $this->rawItems();

        if (! isset($items[$productId])) {
            throw ValidationException::withMessages([
                'cart' => 'That product is no longer in your cart.',
            ]);
        }

        $product = $this->activeProduct($productId);
        $stock = (int) $product->stock_quantity;

        if ($stock <= 0) {
            throw ValidationException::withMessages([
                'product' => 'This product is currently out of stock.',
            ]);
        }

        if ($quantity > $stock) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$stock} unit(s) of {$product->name} are currently available.",
            ]);
        }

        $items[$productId] = [
            'product_id' => $productId,
            'quantity' => max(1, $quantity),
        ];

        $this->writeItems($items);
    }

    public function remove(int $productId): void
    {
        $items = $this->rawItems();
        unset($items[$productId]);
        $this->writeItems($items);
        $this->forgetCartIfEmpty();
    }

    /**
     * @param array<int, int|string> $productIds
     */
    public function removeMany(array $productIds): int
    {
        $items = $this->rawItems();
        $removed = 0;

        foreach (array_unique(array_map('intval', $productIds)) as $productId) {
            if (! isset($items[$productId])) {
                continue;
            }

            unset($items[$productId]);
            $removed++;
        }

        $this->writeItems($items);
        $this->forgetCartIfEmpty();

        return $removed;
    }

    public function clear(): void
    {
        session()->forget([self::ITEMS_SESSION_KEY, self::LEGACY_SESSION_KEY, self::COUPON_SESSION_KEY]);
    }

    public function applyCoupon(string $code): void
    {
        $summary = $this->summary();

        if ($summary['items']->isEmpty()) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Add at least one product before applying a coupon.',
            ]);
        }

        $coupon = $this->couponService->resolveApplicableCoupon($code, (float) $summary['subtotal']);
        session()->put(self::COUPON_SESSION_KEY, $coupon->code);
    }

    public function removeCoupon(): void
    {
        session()->forget(self::COUPON_SESSION_KEY);
    }

    private function activeProduct(int $productId): Product
    {
        $product = Product::query()
            ->whereKey($productId)
            ->where('status', 'active')
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'product' => 'This product is not available for purchase.',
            ]);
        }

        return $product;
    }

    /**
     * @return array<int, array{product_id:int,quantity:int}>
     */
    private function rawItems(): array
    {
        $items = session()->get(self::ITEMS_SESSION_KEY);

        if (! is_array($items)) {
            $legacy = session()->get(self::LEGACY_SESSION_KEY, []);
            $items = is_array($legacy) ? $legacy : [];
        }

        $normalized = [];

        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $productId = (int) ($item['product_id'] ?? $item['id'] ?? $key);
                $quantity = max(1, (int) ($item['quantity'] ?? 1));
            } else {
                $productId = (int) $key;
                $quantity = max(1, (int) $item);
            }

            if ($productId <= 0) {
                continue;
            }

            $normalized[$productId] = [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, array{product_id:int,quantity:int}> $items
     */
    private function writeItems(array $items): void
    {
        session()->put(self::ITEMS_SESSION_KEY, $items);
        session()->forget(self::LEGACY_SESSION_KEY);
    }

    /**
     * @return array{0:mixed,1:float,2:?string}
     */
    private function resolveCoupon(float $subtotal): array
    {
        $code = session()->get(self::COUPON_SESSION_KEY);

        if (! is_string($code) || trim($code) === '' || $subtotal <= 0) {
            return [null, 0.0, null];
        }

        try {
            $coupon = $this->couponService->resolveApplicableCoupon($code, $subtotal);

            return [
                $coupon,
                $this->couponService->calculateDiscount($coupon, $subtotal),
                null,
            ];
        } catch (ValidationException $exception) {
            session()->forget(self::COUPON_SESSION_KEY);
            $messages = $exception->errors()['coupon_code'] ?? [];

            return [null, 0.0, $messages[0] ?? 'The applied coupon is no longer valid.'];
        }
    }

    /**
     * @param Collection<int, array<string, mixed>> $items
     */
    private function recommendedProducts(Collection $items): Collection
    {
        $excludedIds = $items->pluck('product_id')->map(fn ($id) => (int) $id)->all();
        $categoryIds = $items
            ->map(fn (array $item) => $item['product']->category_id)
            ->filter()
            ->unique()
            ->values();

        $baseQuery = Product::query()
            ->where('status', 'active')
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
                ->where('status', 'active')
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

    /**
     * @return array<string, mixed>
     */
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

    private function forgetCartIfEmpty(): void
    {
        if ($this->rawItems() === []) {
            session()->forget([self::ITEMS_SESSION_KEY, self::LEGACY_SESSION_KEY, self::COUPON_SESSION_KEY]);
        }
    }
}
