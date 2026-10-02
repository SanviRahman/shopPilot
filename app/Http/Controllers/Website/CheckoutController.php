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

    public function index(): View|RedirectResponse
    {
        $data = $this->checkoutService->pageData();

        if ($data['items']->isEmpty()) {
            return redirect()->route('website.cart.index')->withErrors([
                'cart' => 'Your cart is empty. Add products before checkout.',
            ]);
        }

        if (! $data['canCheckout']) {
            return redirect()->route('website.cart.index')->withErrors([
                'cart' => 'Remove unavailable products before checkout.',
            ]);
        }

        if ($data['paymentMethods']->isEmpty()) {
            return redirect()->route('website.cart.index')->withErrors([
                'payment' => 'No active manual payment method is currently available.',
            ]);
        }

        return view('website.checkout.index', $data);
    }

    public function store(CheckoutRequest $request): JsonResponse|RedirectResponse
    {
        $order = $this->checkoutService->placeOrder($request->validated());

        $metaEvent = [
            'name' => 'Purchase',
            'payload' => [
                'value' => (float) $order->grand_total,
                'currency' => 'BDT',
                'content_type' => 'product',
                'content_ids' => $order->items->pluck('product_id')->map(fn ($id) => (string) $id)->values()->all(),
                'num_items' => (int) $order->items->sum('quantity'),
                'order_id' => $order->order_number,
            ],
        ];

        $redirectUrl = route('website.checkout.thank-you', $order->order_number);
        $message = 'Order placed successfully. Your payment information has been submitted for verification.';

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => $redirectUrl,
                'meta_event' => $metaEvent,
                'cart' => [
                    'count' => 0,
                    'subtotal' => 0,
                    'discount' => 0,
                    'grand_total' => 0,
                    'coupon_code' => null,
                    'can_checkout' => false,
                ],
            ]);
        }

        return redirect()
            ->to($redirectUrl)
            ->with('success', $message)
            ->with('meta_event', $metaEvent);
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
        $this->cartService->applyCoupon($request->validated('coupon_code'));

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully.',
            'summary' => $this->summaryPayload(),
        ]);
    }

    public function removeCoupon(): JsonResponse
    {
        $this->cartService->removeCoupon();

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed.',
            'summary' => $this->summaryPayload(),
        ]);
    }

    private function summaryPayload(): array
    {
        $summary = $this->cartService->summary();

        return [
            'subtotal' => $summary['subtotal'],
            'discount' => $summary['discount'],
            'grand_total' => $summary['grandTotal'],
            'coupon_code' => $summary['coupon']?->code,
            'count' => $summary['count'],
        ];
    }
}
