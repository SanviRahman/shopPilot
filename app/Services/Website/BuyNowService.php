<?php

namespace App\Services\Website;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CouponService;
use Illuminate\Validation\ValidationException;

class BuyNowService
{
    private const SESSION_ROOT = 'shoppilot_buy_now';
    private const ITEM_SESSION_KEY = 'shoppilot_buy_now.item';
    private const COUPON_SESSION_KEY = 'shoppilot_buy_now.coupon_code';

    public function __construct(private readonly CouponService $couponService)
    {
    }

    public function start(int $productId, int $quantity = 1, ?int $variantId = null): void
    {
        $product = $this->activeProduct($productId);
        $variant = $this->resolveVariant($product, $variantId);
        $stock = $variant ? $variant->available_quantity : (int) $product->stock_quantity;
        $quantity = max(1, $quantity);

        if ($stock <= 0 || $quantity > $stock) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$stock} unit(s) of {$product->name} are currently available for this option.",
            ]);
        }

        session()->put(self::ITEM_SESSION_KEY, [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
        ]);
        session()->forget(self::COUPON_SESSION_KEY);
    }

    public function summary(): array
    {
        $storedItem = session()->get(self::ITEM_SESSION_KEY);
        if (! is_array($storedItem)) return $this->emptySummary();

        $productId = (int) ($storedItem['product_id'] ?? 0);
        $variantId = isset($storedItem['variant_id']) ? (int) $storedItem['variant_id'] : null;
        $quantity = max(1, (int) ($storedItem['quantity'] ?? 1));
        $product = Product::query()
            ->published()
            ->whereKey($productId)
            ->with(['category', 'media'])
            ->first();

        if (! $product) {
            $this->clear();
            return $this->emptySummary();
        }

        try {
            $variant = $this->resolveVariant($product, $variantId);
        } catch (ValidationException) {
            $this->clear();
            return $this->emptySummary();
        }

        $stock = $variant ? $variant->available_quantity : max(0, (int) $product->stock_quantity);
        $regularPrice = (float) ($variant?->regular_price ?? $product->regular_price);
        $salePrice = $variant
            ? ($variant->sale_price !== null ? (float) $variant->sale_price : null)
            : ($product->sale_price !== null ? (float) $product->sale_price : null);
        $hasSale = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
        $unitPrice = $hasSale ? $salePrice : $regularPrice;
        $lineTotal = round($unitPrice * $quantity, 2);
        $discountPercent = $hasSale && $regularPrice > 0 ? (int) round((($regularPrice - $salePrice) / $regularPrice) * 100) : 0;
        $variantLabel = $variant?->attributeValues->map(fn ($value) => $value->attribute->name . ': ' . $value->label)->implode(' / ');

        $items = collect([[
            'product' => $product,
            'variant' => $variant,
            'variant_label' => $variantLabel,
            'sku' => $variant?->sku ?? $product->sku,
            'image_url' => $variant?->image_url ?: $product->thumbnail_url,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $quantity,
            'stock_quantity' => $stock,
            'in_stock' => $stock > 0 && $quantity <= $stock,
            'regular_price' => $regularPrice,
            'unit_price' => $unitPrice,
            'has_sale' => $hasSale,
            'discount_percent' => $discountPercent,
            'line_total' => $lineTotal,
        ]]);

        [$coupon, $discount, $couponInvalidMessage] = $this->resolveCoupon($lineTotal);
        $canCheckout = $stock > 0 && $quantity <= $stock;

        return [
            'items' => $items,
            'count' => $quantity,
            'subtotal' => $lineTotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'couponInvalidMessage' => $couponInvalidMessage,
            'shipping' => null,
            'shippingLabel' => 'Calculated at checkout',
            'grandTotal' => round(max(0, $lineTotal - $discount), 2),
            'hasUnavailableItems' => ! $canCheckout,
            'canCheckout' => $canCheckout,
        ];
    }

    public function checkoutItems(): array
    {
        $item = session()->get(self::ITEM_SESSION_KEY);
        if (! is_array($item) || (int) ($item['product_id'] ?? 0) <= 0) return [];

        return [[
            'product_id' => (int) $item['product_id'],
            'variant_id' => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
            'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
        ]];
    }

    public function appliedCouponCode(): ?string
    {
        $code = session()->get(self::COUPON_SESSION_KEY);
        return is_string($code) && trim($code) !== '' ? strtoupper(trim($code)) : null;
    }

    public function applyCoupon(string $code): void
    {
        $summary = $this->summary();
        if ($summary['items']->isEmpty()) {
            throw ValidationException::withMessages(['coupon_code' => 'Start a Buy Now checkout before applying a coupon.']);
        }
        $coupon = $this->couponService->resolveApplicableCoupon($code, (float) $summary['subtotal']);
        session()->put(self::COUPON_SESSION_KEY, $coupon->code);
    }

    public function removeCoupon(): void
    {
        session()->forget(self::COUPON_SESSION_KEY);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_ROOT);
    }

    private function activeProduct(int $productId): Product
    {
        $product = Product::query()->published()->whereKey($productId)->first();
        if (! $product) throw ValidationException::withMessages(['product' => 'This product is not available for purchase.']);
        return $product;
    }

    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        if ($product->product_type !== 'variable') {
            if ($variantId) throw ValidationException::withMessages(['variant_id' => 'This product does not have selectable variations.']);
            return null;
        }

        $variant = $variantId
            ? ProductVariant::query()
                ->whereKey($variantId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->with(['inventoryBalance', 'attributeValues.attribute'])
                ->first()
            : null;

        if (! $variant || ! $variant->inventoryBalance) {
            throw ValidationException::withMessages(['variant_id' => 'Please select an available product variation.']);
        }

        return $variant;
    }

    private function resolveCoupon(float $subtotal): array
    {
        $code = $this->appliedCouponCode();
        if ($code === null || $subtotal <= 0) return [null, 0.0, null];

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
