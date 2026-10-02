<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\AddToCartRequest;
use App\Http\Requests\Website\ApplyCartCouponRequest;
use App\Http\Requests\Website\RemoveCartItemsRequest;
use App\Http\Requests\Website\UpdateCartItemRequest;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index(): View
    {
        return view('website.cart.index', $this->cartService->getCartPageData());
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->cartService->add((int) $data['product_id'], (int) ($data['quantity'] ?? 1));

        if (($data['redirect_to'] ?? 'back') === 'cart') {
            return redirect()->route('website.cart.index')->with('success', 'Product added to your cart.');
        }

        return back()->with('success', 'Product added to your cart.');
    }

    public function update(UpdateCartItemRequest $request, int $productId): RedirectResponse
    {
        $this->cartService->update($productId, (int) $request->validated('quantity'));

        return redirect()->route('website.cart.index')->with('success', 'Cart quantity updated.');
    }

    public function destroy(int $productId): RedirectResponse
    {
        $this->cartService->remove($productId);

        return redirect()->route('website.cart.index')->with('success', 'Product removed from your cart.');
    }

    public function removeSelected(RemoveCartItemsRequest $request): RedirectResponse
    {
        $removed = $this->cartService->removeMany($request->validated('product_ids'));

        return redirect()->route('website.cart.index')->with(
            'success',
            $removed === 1 ? 'Selected product removed.' : "{$removed} selected products removed.",
        );
    }

    public function clear(): RedirectResponse
    {
        $this->cartService->clear();

        return redirect()->route('website.cart.index')->with('success', 'Your cart has been cleared.');
    }

    public function applyCoupon(ApplyCartCouponRequest $request): RedirectResponse
    {
        $this->cartService->applyCoupon($request->validated('coupon_code'));

        return redirect()->route('website.cart.index')->with('success', 'Coupon applied successfully.');
    }

    public function removeCoupon(): RedirectResponse
    {
        $this->cartService->removeCoupon();

        return redirect()->route('website.cart.index')->with('success', 'Coupon removed.');
    }
}
