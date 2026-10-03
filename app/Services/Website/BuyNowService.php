<?php

namespace App\Services\Website;

use App\Models\Product;
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

    public function start(int $productId, int $quantity = 1): void
    {
        $product = $this->activeProduct($productId);
        $stock = (int) $product->stock_quantity;
        $quantity = max(1, $quantity);

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

        session()->put(self::ITEM_SESSION_KEY, [
            'product_id' => $product->getKey(),
            'quantity' => $quantity,
        ]);

        // A new direct-purchase intent must not inherit a coupon from an older one.
        session()->forget(self::COUPON_SESSION_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $storedItem = session()->get(self::ITEM_SESSION_KEY);

        if (! is_array($storedItem)) {
            return $this->emptySummary();
        }

        $productId = (int) ($storedItem['product_id'] ?? 0);
        $quantity = max(1, (int) ($storedItem['quantity'] ?? 1));

        if ($productId <= 0) {
            $this->clear();

            return $this->emptySummary();
        }

        $product = Product::query()
            ->whereKey($productId)
            ->where('status', 'active')
            ->with(['category', 'media'])
            ->first();

        if (! $product) {
            $this->clear();

            return $this->emptySummary();
        }

        $stock = max(0, (int) $product->stock_quantity);
        $regularPrice = (float) $product->regular_price;
        $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;
        $hasSale = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
        $unitPrice = $hasSale ? $salePrice : $regularPrice;
        $lineTotal = round($unitPrice * $quantity, 2);
        $discountPercent = $hasSale && $regularPrice > 0
            ? (int) round((($regularPrice - $salePrice) / $regularPrice) * 100)
            : 0;

        $items = collect([[
            'product' => $product,
            'product_id' => $productId,
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
        $grandTotal = round(max(0, $lineTotal - $discount), 2);
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
            'grandTotal' => $grandTotal,
            'hasUnavailableItems' => ! $canCheckout,
            'canCheckout' => $canCheckout,
        ];
    }

    /**
     * @return array<int, array{product_id:int,quantity:int}>
     */
    public function checkoutItems(): array
    {
        $item = session()->get(self::ITEM_SESSION_KEY);

        if (! is_array($item)) {
            return [];
        }

        $productId = (int) ($item['product_id'] ?? 0);
        $quantity = max(1, (int) ($item['quantity'] ?? 1));

        if ($productId <= 0) {
            return [];
        }

        return [
            $productId => [
                'product_id' => $productId,
                'quantity' => $quantity,
            ],
        ];
    }

    public function appliedCouponCode(): ?string
    {
        $code = session()->get(self::COUPON_SESSION_KEY);

        return is_string($code) && trim($code) !== ''
            ? strtoupper(trim($code))
            : null;
    }

    public function applyCoupon(string $code): void
    {
        $summary = $this->summary();

        if ($summary['items']->isEmpty()) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Start a Buy Now checkout before applying a coupon.',
            ]);
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
     * @return array{0:mixed,1:float,2:?string}
     */
    private function resolveCoupon(float $subtotal): array
    {
        $code = $this->appliedCouponCode();

        if ($code === null || $subtotal <= 0) {
            return [null, 0.0, null];
        }

        try {
            $coupon = $this->couponService->resolveApplicableCoupon($code, $subtotal);
            session()->put(self::COUPON_SESSION_KEY, $coupon->code);

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
}
