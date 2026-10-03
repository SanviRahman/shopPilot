<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\AddToCartRequest;
use App\Http\Requests\Website\ApplyCartCouponRequest;
use App\Http\Requests\Website\RemoveCartItemsRequest;
use App\Http\Requests\Website\UpdateCartItemRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $data = $this->cartService->getCartPageData();

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'html' => view('website.cart.partials.ajax-content', $data)->render(),
                'cart' => $this->cartPayload(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('website.cart.index', $data);
    }

    public function store(AddToCartRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $this->cartService->add((int) $data['product_id'], (int) ($data['quantity'] ?? 1));

        $metaEvent = [
            'name' => 'AddToCart',
            'payload' => [
                'content_ids' => [(string) $data['product_id']],
                'content_type' => 'product',
                'contents' => [['id' => (string) $data['product_id'], 'quantity' => (int) ($data['quantity'] ?? 1)]],
            ],
        ];

        $redirectTo = $data['redirect_to'] ?? 'back';

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to your cart.',
                'cart' => $this->cartPayload(),
                'meta_event' => $metaEvent,
                'redirect_url' => $redirectTo === 'cart' ? route('website.cart.index') : null,
            ]);
        }

        if ($redirectTo === 'cart') {
            return redirect()->route('website.cart.index')
                ->with('success', 'Product added to your cart.')
                ->with('meta_event', $metaEvent);
        }

        return back()->with('success', 'Product added to your cart.')->with('meta_event', $metaEvent);
    }

    public function update(UpdateCartItemRequest $request, int $productId): JsonResponse|RedirectResponse
    {
        $this->cartService->update($productId, (int) $request->validated('quantity'));

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Cart quantity updated.',
                'cart' => $this->cartPayload(),
            ]);
        }

        return redirect()->route('website.cart.index')->with('success', 'Cart quantity updated.');
    }

    public function destroy(Request $request, int $productId): JsonResponse|RedirectResponse
    {
        $this->cartService->remove($productId);

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Product removed from your cart.',
                'cart' => $this->cartPayload(),
            ]);
        }

        return redirect()->route('website.cart.index')->with('success', 'Product removed from your cart.');
    }

    public function removeSelected(RemoveCartItemsRequest $request): JsonResponse|RedirectResponse
    {
        $removed = $this->cartService->removeMany($request->validated('product_ids'));
        $message = $removed === 1 ? 'Selected product removed.' : "{$removed} selected products removed.";

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cart' => $this->cartPayload(),
            ]);
        }

        return redirect()->route('website.cart.index')->with('success', $message);
    }

    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $this->cartService->clear();

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Your cart has been cleared.',
                'cart' => $this->cartPayload(),
            ]);
        }

        return redirect()->route('website.cart.index')->with('success', 'Your cart has been cleared.');
    }

    public function applyCoupon(ApplyCartCouponRequest $request): JsonResponse|RedirectResponse
    {
        $this->cartService->applyCoupon($request->validated('coupon_code'));

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully.',
                'cart' => $this->cartPayload(),
            ]);
        }

        return redirect()->route('website.cart.index')->with('success', 'Coupon applied successfully.');
    }

    public function removeCoupon(Request $request): JsonResponse|RedirectResponse
    {
        $this->cartService->removeCoupon();

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon removed.',
                'cart' => $this->cartPayload(),
            ]);
        }

        return redirect()->route('website.cart.index')->with('success', 'Coupon removed.');
    }

    private function wantsJson(Request $request): bool
    {
        return $request->ajax() || $request->expectsJson();
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
}
