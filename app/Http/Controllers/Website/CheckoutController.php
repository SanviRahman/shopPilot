<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\ApplyCartCouponRequest;
use App\Http\Requests\Website\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\Website\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly CartService $cartService,
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $checkoutMode = $this->normalizeCheckoutMode((string) $request->query('mode', 'cart'));
        $data = $this->checkoutService->pageData($checkoutMode);

        if ($data['items']->isEmpty()) {
            $message = $checkoutMode === 'buy_now'
                ? 'Your Buy Now session has expired. Please choose the product again.'
                : 'Your cart is empty. Add products before checkout.';

            return redirect()->route($checkoutMode === 'buy_now' ? 'website.shop' : 'website.cart.index')
                ->withErrors(['cart' => $message]);
        }

        if (! $data['canCheckout']) {
            return redirect()->route($checkoutMode === 'buy_now' ? 'website.shop' : 'website.cart.index')
                ->withErrors([
                    'cart' => $checkoutMode === 'buy_now'
                        ? 'The selected Buy Now product is no longer available in the requested quantity.'
                        : 'Remove unavailable products before checkout.',
                ]);
        }

        if ($data['paymentMethods']->isEmpty()) {
            return redirect()->route($checkoutMode === 'buy_now' ? 'website.shop' : 'website.cart.index')
                ->withErrors([
                    'payment' => 'No active manual payment method is currently available.',
                ]);
        }

        return view('website.checkout.index', $data);
    }

    public function store(CheckoutRequest $request): JsonResponse|RedirectResponse
    {
        $order = $this->checkoutService->placeOrder($request->validated());

        $purchasePayload = ['value' => (float) $order->grand_total, 'currency' => 'BDT', 'content_type' => 'product', 'content_ids' => $order->items->pluck('product_id')->map(fn ($id) => (string) $id)->values()->all(), 'num_items' => (int) $order->items->sum('quantity'), 'order_id' => $order->order_number];
        $metaEvents = [['name' => 'Purchase', 'payload' => $purchasePayload], ['name' => 'PurchaseSuccess', 'payload' => [...$purchasePayload, 'status' => 'success'], 'custom' => true]];
        $redirectUrl = route('website.checkout.thank-you', $order->order_number);
        $message = 'Order placed successfully. Your payment information has been submitted for verification.';
        if ($request->ajax() || $request->expectsJson()) { $request->session()->flash('meta_events', $metaEvents); return response()->json(['success' => true, 'message' => $message, 'redirect_url' => $redirectUrl, 'meta_events' => $metaEvents, 'cart' => $this->cartPayload()]); }
        return redirect()->to($redirectUrl)->with('success', $message)->with('meta_events', $metaEvents);
    }

    public function thankYou(string $orderNumber): View
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->with(['items', 'paymentSubmission.paymentMethod'])
            ->firstOrFail();

        abort_unless($this->checkoutService->canViewThankYou($order), 404);

        return view('website.checkout.thank-you', compact('order'));
    }

    public function applyCoupon(ApplyCartCouponRequest $request): JsonResponse
    {
        $checkoutMode = $this->normalizeCheckoutMode((string) $request->input('checkout_mode', 'cart'));
        $this->checkoutService->applyCoupon($checkoutMode, $request->validated('coupon_code'));

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully.',
            'summary' => $this->summaryPayload($checkoutMode),
        ]);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $checkoutMode = $this->normalizeCheckoutMode((string) $request->input('checkout_mode', 'cart'));
        $this->checkoutService->removeCoupon($checkoutMode);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed.',
            'summary' => $this->summaryPayload($checkoutMode),
        ]);
    }

    private function summaryPayload(string $checkoutMode): array
    {
        $summary = $this->checkoutService->summaryForMode($checkoutMode);

        return [
            'subtotal' => $summary['subtotal'],
            'discount' => $summary['discount'],
            'grand_total' => $summary['grandTotal'],
            'coupon_code' => $summary['coupon']?->code,
            'count' => $summary['count'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cartPayload(): array
    {
        $summary = $this->cartService->summary();

        return [
            'count' => (int) $summary['count'],
            'subtotal' => (float) $summary['subtotal'],
            'discount' => (float) $summary['discount'],
            'shipping' => $summary['shipping'],
            'grand_total' => (float) $summary['grandTotal'],
            'coupon_code' => $summary['coupon']?->code,
            'can_checkout' => (bool) $summary['canCheckout'],
        ];
    }

    private function normalizeCheckoutMode(string $checkoutMode): string
    {
        return in_array($checkoutMode, ['buy_now', 'buy-now'], true)
            ? 'buy_now'
            : 'cart';
    }
}
