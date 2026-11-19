<?php

namespace App\Services\Website;

use App\Models\CommissionEntry;
use App\Models\CommissionRule;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\PaymentSubmission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\VendorOrder;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderHistoryService;
use Illuminate\Support\Collection;
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
        $shippingContext = $this->shippingContext($summary['items']);
        $paymentCodes = (array) config('shop.checkout.manual_payment_codes', []);
        $user = auth('web')->user();
        $shippingMethods = (array) config('shop.checkout.shipping_methods', []);
        $defaultShipping = (float) ($shippingMethods['standard']['fee'] ?? 0);

        return [
            ...$summary,
            'grandTotal' => round($summary['subtotal'] - $summary['discount'] + ($defaultShipping * $shippingContext['seller_count']) + $shippingContext['vendor_shipping_total'], 2),
            'checkoutMode' => $checkoutMode,
            'shippingSellerCount' => $shippingContext['seller_count'],
            'vendorShippingTotal' => $shippingContext['vendor_shipping_total'],
            'paymentMethods' => PaymentMethod::query()
                ->where('status', 'active')
                ->whereIn('code', $paymentCodes)
                ->orderBy('id')
                ->get(),
            'shippingMethods' => $shippingMethods,
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
            $productIds = collect($sessionItems)->pluck('product_id')->map(fn ($id) => (int) $id)->filter()->unique()->values();
            $variantIds = collect($sessionItems)->pluck('variant_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->published()
                ->with('vendor.store')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $productIds->count()) {
                throw ValidationException::withMessages(['cart' => 'One or more products in your cart are no longer available.']);
            }

            $variants = ProductVariant::query()
                ->whereIn('id', $variantIds)
                ->where('is_active', true)
                ->with('attributeValues.attribute')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $balances = InventoryBalance::query()
                ->where(function ($query) use ($productIds, $variantIds) {
                    $query->whereIn('product_id', $productIds);
                    if ($variantIds->isNotEmpty()) {
                        $query->orWhereIn('product_variant_id', $variantIds);
                    }
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $balancesByProduct = $balances->whereNotNull('product_id')->keyBy('product_id');
            $balancesByVariant = $balances->whereNotNull('product_variant_id')->keyBy('product_variant_id');
            $snapshots = [];
            $subtotal = 0.0;

            foreach ($sessionItems as $storedItem) {
                $productId = (int) $storedItem['product_id'];
                $variantId = isset($storedItem['variant_id']) ? (int) $storedItem['variant_id'] : null;
                $quantity = max(1, (int) $storedItem['quantity']);
                $product = $products->get($productId);
                $variant = $variantId ? $variants->get($variantId) : null;

                if ($product->product_type === 'variable' && (! $variant || (int) $variant->product_id !== $productId)) {
                    throw ValidationException::withMessages(['cart' => "A selected variation of {$product->name} is no longer available."]);
                }
                if ($variantId && ($product->product_type !== 'variable' || ! $variant || (int) $variant->product_id !== $productId)) {
                    throw ValidationException::withMessages(['cart' => "The selected product option for {$product->name} is not valid."]);
                }

                if ($variant) {
                    $balance = $balancesByVariant->get($variant->id);
                    if (! $balance) {
                        throw ValidationException::withMessages(['cart' => "A selected variation of {$product->name} has no inventory record."]);
                    }
                } else {
                    $balance = $balancesByProduct->get($product->id);
                    if (! $balance) {
                        $balance = InventoryBalance::create([
                            'product_id' => $product->id,
                            'on_hand_quantity' => (int) $product->stock_quantity,
                            'reserved_quantity' => 0,
                        ]);
                        $balancesByProduct->put($product->id, $balance);
                    }
                }

                $stock = (int) $balance->available_quantity;
                if ($stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Only {$stock} unit(s) of {$product->name} are available for the selected option.",
                    ]);
                }

                $regularPrice = (float) ($variant?->regular_price ?? $product->regular_price);
                $salePrice = $variant
                    ? ($variant->sale_price !== null ? (float) $variant->sale_price : null)
                    : ($product->sale_price !== null ? (float) $product->sale_price : null);
                $unitPrice = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice ? $salePrice : $regularPrice;
                $lineTotal = round($unitPrice * $quantity, 2);
                $variantAttributes = $variant?->attributeValues->map(fn ($value) => [
                    'attribute' => $value->attribute->name,
                    'value' => $value->label,
                ])->values()->all();
                $variantName = $variantAttributes
                    ? collect($variantAttributes)->map(fn ($item) => $item['attribute'] . ': ' . $item['value'])->implode(' / ')
                    : null;
                $vendorId = $product->vendor_id ? (int) $product->vendor_id : null;

                $snapshots[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'balance' => $balance,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'vendor_id' => $vendorId,
                    'seller_key' => $vendorId ? 'vendor-' . $vendorId : 'platform',
                    'product_name' => $product->name,
                    'sku' => $variant?->sku ?? $product->sku,
                    'variant_name' => $variantName,
                    'variant_attributes' => $variantAttributes,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                    'discount_amount' => 0.0,
                ];
                $subtotal += $lineTotal;
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

            $remainingDiscount = round($discount, 2);
            foreach ($snapshots as $index => &$snapshot) {
                $allocated = $index === array_key_last($snapshots)
                    ? $remainingDiscount
                    : round($discount * ($snapshot['line_total'] / max(0.01, $subtotal)), 2);
                $snapshot['discount_amount'] = min($remainingDiscount, max(0, $allocated));
                $remainingDiscount = round($remainingDiscount - $snapshot['discount_amount'], 2);
            }
            unset($snapshot);

            $shippingMethod = $this->shippingMethod((string) $data['delivery_method']);
            $baseShipping = round((float) ($shippingMethod['fee'] ?? 0), 2);
            $paymentMethod = $this->activeManualPaymentMethod((int) $data['payment_method_id']);
            $commissionRule = CommissionRule::activeMarketplaceRule(true);
            $currency = config('shop.marketplace.currency_code');
            $groups = collect($snapshots)->groupBy('seller_key');
            $groupData = [];
            $shipping = 0.0;
            $discountTotal = 0.0;

            foreach ($groups as $sellerKey => $items) {
                $vendorId = $items->first()['vendor_id'];
                $storeFee = $vendorId ? (float) ($items->first()['product']->vendor?->store?->shipping_fee ?? 0) : 0.0;
                $groupSubtotal = round((float) $items->sum('line_total'), 2);
                $groupDiscount = round((float) $items->sum('discount_amount'), 2);
                $groupShipping = round($baseShipping + $storeFee, 2);
                $basis = round(max(0, $groupSubtotal - $groupDiscount), 2);
                $rate = $vendorId ? (float) $commissionRule->rate_value : 0.0;
                $commission = round($basis * ($rate / 100), 2);
                $earning = $vendorId ? round($basis - $commission + $groupShipping, 2) : 0.0;
                $groupData[$sellerKey] = [
                    'vendor_id' => $vendorId,
                    'suborder_number' => $this->uniqueSuborderNumber(),
                    'subtotal' => $groupSubtotal,
                    'discount' => $groupDiscount,
                    'shipping_fee' => $groupShipping,
                    'grand_total' => round($groupSubtotal - $groupDiscount + $groupShipping, 2),
                    'commission_rate' => $rate,
                    'commission_basis_amount' => $basis,
                    'commission_amount' => $commission,
                    'vendor_earning' => $earning,
                ];

                $shipping += $groupShipping;
                $discountTotal += $groupDiscount;
            }

            $grandTotal = round($subtotal - $discountTotal + $shipping, 2);
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
                'discount' => $discountTotal,
                'shipping' => round($shipping, 2),
                'grand_total' => $grandTotal,
                'payment_status' => Order::PAYMENT_SUBMITTED,
                'order_status' => Order::STATUS_PENDING,
                'customer_note' => $data['customer_note'] ?: null,
                'internal_note' => null,
            ]);

            foreach ($groupData as $sellerKey => $group) {
                $vendorOrder = VendorOrder::create([
                    'order_id' => $order->id,
                    'vendor_id' => $group['vendor_id'],
                    'seller_key' => $sellerKey,
                    'suborder_number' => $group['suborder_number'],
                    'subtotal' => $group['subtotal'],
                    'discount' => $group['discount'],
                    'shipping_fee' => $group['shipping_fee'],
                    'grand_total' => $group['grand_total'],
                    'commission_rate' => $group['commission_rate'],
                    'commission_basis_amount' => $group['commission_basis_amount'],
                    'commission_amount' => $group['commission_amount'],
                    'vendor_earning' => $group['vendor_earning'],
                    'currency_code' => $currency,
                    'payment_status' => Order::PAYMENT_SUBMITTED,
                    'status' => VendorOrder::STATUS_AWAITING_PAYMENT,
                ]);
                $groupData[$sellerKey] = $vendorOrder;

                if ($group['vendor_id']) {
                    CommissionEntry::create([
                        'vendor_order_id' => $vendorOrder->id,
                        'commission_rule_id' => $commissionRule->id,
                        'basis_amount' => $group['commission_basis_amount'],
                        'rate_value' => $group['commission_rate'],
                        'commission_amount' => $group['commission_amount'],
                        'vendor_earning' => $group['vendor_earning'],
                        'currency_code' => $currency,
                        'rule_snapshot' => [
                            'scope_type' => $commissionRule->scope_type,
                            'commission_basis' => $commissionRule->commission_basis,
                            'rate_value' => $group['commission_rate'],
                            'effective_from' => $commissionRule->effective_from?->toISOString(),
                        ],
                    ]);
                }
            }

            foreach ($snapshots as $snapshot) {
                $vendorOrder = $groupData[$snapshot['seller_key']];
                $item = OrderItem::create([
                    'order_id' => $order->id,
                    'vendor_order_id' => $vendorOrder->id,
                    'product_id' => $snapshot['product_id'],
                    'product_variant_id' => $snapshot['product_variant_id'],
                    'variant_name' => $snapshot['variant_name'],
                    'variant_attributes' => $snapshot['variant_attributes'],
                    'product_name' => $snapshot['product_name'],
                    'sku' => $snapshot['sku'],
                    'unit_price' => $snapshot['unit_price'],
                    'quantity' => $snapshot['quantity'],
                    'line_total' => $snapshot['line_total'],
                    'discount_amount' => $snapshot['discount_amount'],
                ]);

                $balance = $snapshot['balance'];
                $balance->reserved_quantity = (int) $balance->reserved_quantity + $snapshot['quantity'];
                $balance->save();

                InventoryReservation::create([
                    'order_item_id' => $item->id,
                    'inventory_balance_id' => $balance->id,
                    'product_id' => $snapshot['product_id'],
                    'product_variant_id' => $snapshot['product_variant_id'],
                    'quantity' => $snapshot['quantity'],
                    'status' => 'active',
                ]);

                InventoryMovement::create([
                    'inventory_balance_id' => $balance->id,
                    'product_id' => $snapshot['product_id'],
                    'product_variant_id' => $snapshot['product_variant_id'],
                    'movement_type' => 'reserve',
                    'quantity_change' => 0,
                    'on_hand_before' => (int) $balance->on_hand_quantity,
                    'on_hand_after' => (int) $balance->on_hand_quantity,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'note' => 'Stock reserved for customer order.',
                ]);
            }

            $this->syncProductStock($snapshots);

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
                    note: 'Marketplace order placed. Stock reserved and payment information submitted for verification.',
                );
            } else {
                $this->orderHistoryService->recordSystemEvent(
                    order: $order,
                    fromStatus: null,
                    toStatus: Order::STATUS_PENDING,
                    note: 'Guest marketplace order placed. Stock reserved and payment information submitted for verification.',
                );
            }

            return $order->load(['items', 'vendorOrders.vendor.store', 'paymentSubmission.paymentMethod', 'coupon']);
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

    private function shippingContext(Collection $items): array
    {
        $vendorIds = $items->pluck('product.vendor_id')->filter()->unique()->values();
        $stores = $vendorIds->isEmpty()
            ? collect()
            : Store::query()->whereIn('vendor_id', $vendorIds)->get()->keyBy('vendor_id');
        $sellerKeys = $items->map(fn ($item) => $item['product']->vendor_id ? 'vendor-' . $item['product']->vendor_id : 'platform')->unique();
        $vendorShippingTotal = $vendorIds->sum(fn ($id) => (float) ($stores->get($id)?->shipping_fee ?? 0));

        return [
            'seller_count' => max(1, $sellerKeys->count()),
            'vendor_shipping_total' => round($vendorShippingTotal, 2),
        ];
    }

    private function syncProductStock(array $snapshots): void
    {
        foreach (collect($snapshots)->pluck('product')->unique('id') as $product) {
            if ($product->product_type === 'simple') {
                $balance = InventoryBalance::query()->where('product_id', $product->id)->first();
                if ($balance) {
                    $product->update(['stock_quantity' => $balance->available_quantity]);
                }
                continue;
            }

            $variantIds = $product->variants()->where('is_active', true)->pluck('id');
            $available = InventoryBalance::query()
                ->whereIn('product_variant_id', $variantIds)
                ->selectRaw('COALESCE(SUM(on_hand_quantity - reserved_quantity), 0) as available')
                ->value('available');
            $product->update(['stock_quantity' => max(0, (int) $available)]);
        }
    }

    private function uniqueSuborderNumber(): string
    {
        do {
            $number = 'SORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(7));
        } while (VendorOrder::query()->where('suborder_number', $number)->exists());

        return $number;
    }

    private function normalizeCheckoutMode(string $checkoutMode): string
    {
        return in_array($checkoutMode, ['buy_now', 'buy-now'], true) ? 'buy_now' : 'cart';
    }

    private function shippingMethod(string $key): array
    {
        $methods = (array) config('shop.checkout.shipping_methods', []);
        if (! isset($methods[$key])) {
            throw ValidationException::withMessages(['delivery_method' => 'Please select a valid delivery method.']);
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
            throw ValidationException::withMessages(['payment_method_id' => 'Please select an active manual payment method.']);
        }

        return $method;
    }
}
