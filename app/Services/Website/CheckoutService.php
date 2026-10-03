<?php

namespace App\Services\Website;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\PaymentSubmission;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderHistoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly BuyNowService $buyNowService,
        private readonly CouponService $couponService,
        private readonly OrderHistoryService $orderHistoryService,
    ) {
    }

    public function pageData(string $checkoutMode = 'cart'): array
    {
        $checkoutMode = $this->normalizeCheckoutMode($checkoutMode);
        $summary = $this->summaryForMode($checkoutMode);
        $paymentCodes = (array) config('shop.checkout.manual_payment_codes', []);
        $user = auth('web')->user();

        return [
            ...$summary,
            'checkoutMode' => $checkoutMode,
            'paymentMethods' => PaymentMethod::query()
                ->where('status', 'active')
                ->whereIn('code', $paymentCodes)
                ->orderBy('id')
                ->get(),
            'shippingMethods' => (array) config('shop.checkout.shipping_methods', []),
            'buyerDefaults' => [
                'name' => $user?->name ?? '',
                'email' => $user?->email ?? '',
                'phone' => $user?->phone_number ?? '',
                'division' => $user?->division ?? '',
                'district' => $user?->district ?? '',
                'upazila' => $user?->upazila ?? '',
                'address' => $user?->address ?? '',
            ],
        ];
    }

    public function placeOrder(array $data): Order
    {
        $checkoutMode = $this->normalizeCheckoutMode((string) ($data['checkout_mode'] ?? 'cart'));
        $sessionItems = $checkoutMode === 'buy_now'
            ? $this->buyNowService->checkoutItems()
            : $this->cartService->checkoutItems();

        if ($sessionItems === []) {
            throw ValidationException::withMessages([
                'cart' => $checkoutMode === 'buy_now'
                    ? 'Your Buy Now session has expired. Please choose the product again.'
                    : 'Your cart is empty. Add at least one product before checkout.',
            ]);
        }

        $order = DB::transaction(function () use ($data, $sessionItems, $checkoutMode): Order {
            $productIds = collect($sessionItems)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $productIds->count()) {
                throw ValidationException::withMessages([
                    'cart' => 'One or more products in your cart are no longer available.',
                ]);
            }

            $itemSnapshots = [];
            $subtotal = 0.0;

            foreach ($sessionItems as $storedItem) {
                $productId = (int) $storedItem['product_id'];
                $quantity = max(1, (int) $storedItem['quantity']);
                $product = $products->get($productId);
                $stock = (int) $product->stock_quantity;

                if ($stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Only {$stock} unit(s) of {$product->name} are available now.",
                    ]);
                }

                $regularPrice = (float) $product->regular_price;
                $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;
                $unitPrice = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice
                    ? $salePrice
                    : $regularPrice;
                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineTotal;

                $itemSnapshots[] = [
                    'product' => $product,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $coupon = null;
            $discount = 0.0;
            $couponCode = $checkoutMode === 'buy_now'
                ? $this->buyNowService->appliedCouponCode()
                : $this->cartService->appliedCouponCode();

            if ($couponCode !== null) {
                $coupon = $this->couponService->resolveApplicableCoupon($couponCode, $subtotal);
                $discount = $this->couponService->calculateDiscount($coupon, $subtotal);
            }

            $shippingMethod = $this->shippingMethod((string) $data['delivery_method']);
            $shipping = round((float) ($shippingMethod['fee'] ?? 0), 2);
            $grandTotal = round(max(0, $subtotal - $discount + $shipping), 2);
            $paymentMethod = $this->activeManualPaymentMethod((int) $data['payment_method_id']);

            do {
                $orderNumber = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            } while (Order::withTrashed()->where('order_number', $orderNumber)->exists());

            $cityOrArea = implode(', ', array_filter([
                $data['upazila'] ?? null,
                $data['district'] ?? null,
                $data['division'] ?? null,
            ]));

            $shippingAddress = trim((string) $data['shipping_address']);
            if (! empty($data['postal_code'])) {
                $shippingAddress .= ', Postal Code: ' . trim((string) $data['postal_code']);
            }

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => auth('web')->id(),
                'assigned_agent_id' => null,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'buyer_name' => $data['buyer_name'],
                'buyer_phone' => $data['buyer_phone'],
                'buyer_email' => $data['buyer_email'],
                'shipping_address' => $shippingAddress,
                'city_or_area' => $cityOrArea,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping' => $shipping,
                'grand_total' => $grandTotal,
                'payment_status' => Order::PAYMENT_SUBMITTED,
                'order_status' => Order::STATUS_PENDING,
                'customer_note' => $data['customer_note'] ?: null,
                'internal_note' => null,
            ]);

            foreach ($itemSnapshots as $snapshot) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $snapshot['product_id'],
                    'product_name' => $snapshot['product_name'],
                    'sku' => $snapshot['sku'],
                    'unit_price' => $snapshot['unit_price'],
                    'quantity' => $snapshot['quantity'],
                    'line_total' => $snapshot['line_total'],
                ]);

                $snapshot['product']->update([
                    'stock_quantity' => (int) $snapshot['product']->stock_quantity - $snapshot['quantity'],
                ]);
            }

            PaymentSubmission::create([
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethod->id,
                'transaction_id' => $data['transaction_id'],
                'amount' => $grandTotal,
                'status' => Order::PAYMENT_SUBMITTED,
                'verified_by_admin_id' => null,
                'verified_at' => null,
                'rejection_note' => null,
            ]);

            if (auth('web')->check()) {
                $this->orderHistoryService->recordCustomerEvent(
                    order: $order,
                    userId: (int) auth('web')->id(),
                    fromStatus: null,
                    toStatus: Order::STATUS_PENDING,
                    note: 'Order placed from checkout. Payment information submitted for verification.',
                );
            } else {
                $this->orderHistoryService->recordSystemEvent(
                    order: $order,
                    fromStatus: null,
                    toStatus: Order::STATUS_PENDING,
                    note: 'Guest order placed from checkout. Payment information submitted for verification.',
                );
            }

            return $order->load(['items', 'paymentSubmission.paymentMethod', 'coupon']);
        });

        session()->put('checkout.last_order_id', $order->id);
        session()->put('checkout.last_order_number', $order->order_number);

        if ($checkoutMode === 'buy_now') {
            $this->buyNowService->clear();
        } else {
            $this->cartService->clear();
        }

        return $order;
    }

    public function canViewThankYou(Order $order): bool
    {
        if ((int) session('checkout.last_order_id') === (int) $order->id) {
            return true;
        }

        return auth('web')->check() && (int) $order->user_id === (int) auth('web')->id();
    }

    /**
     * @return array<string, mixed>
     */
    public function summaryForMode(string $checkoutMode): array
    {
        return $this->normalizeCheckoutMode($checkoutMode) === 'buy_now'
            ? $this->buyNowService->summary()
            : $this->cartService->summary();
    }

    public function applyCoupon(string $checkoutMode, string $code): void
    {
        if ($this->normalizeCheckoutMode($checkoutMode) === 'buy_now') {
            $this->buyNowService->applyCoupon($code);

            return;
        }

        $this->cartService->applyCoupon($code);
    }

    public function removeCoupon(string $checkoutMode): void
    {
        if ($this->normalizeCheckoutMode($checkoutMode) === 'buy_now') {
            $this->buyNowService->removeCoupon();

            return;
        }

        $this->cartService->removeCoupon();
    }

    private function normalizeCheckoutMode(string $checkoutMode): string
    {
        return in_array($checkoutMode, ['buy_now', 'buy-now'], true)
            ? 'buy_now'
            : 'cart';
    }

    private function shippingMethod(string $key): array
    {
        $methods = (array) config('shop.checkout.shipping_methods', []);

        if (! isset($methods[$key])) {
            throw ValidationException::withMessages([
                'delivery_method' => 'Please select a valid delivery method.',
            ]);
        }

        return (array) $methods[$key];
    }

    private function activeManualPaymentMethod(int $id): PaymentMethod
    {
        $codes = (array) config('shop.checkout.manual_payment_codes', []);

        $method = PaymentMethod::query()
            ->whereKey($id)
            ->where('status', 'active')
            ->whereIn('code', $codes)
            ->first();

        if (! $method) {
            throw ValidationException::withMessages([
                'payment_method_id' => 'Please select an active manual payment method.',
            ]);
        }

        return $method;
    }
}
