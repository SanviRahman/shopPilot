<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\StoreWishlistRequest;
use App\Services\CartService;
use App\Services\Website\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlistService,
        private readonly CartService $cartService,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user('web');

        return view('website.account.wishlist.index', [
            ...$this->wishlistService->pageData($user),
            'user' => $user,
        ]);
    }

    public function store(StoreWishlistRequest $request): JsonResponse|RedirectResponse
    {
        $productId = (int) $request->validated('product_id');
        $wishlist = $this->wishlistService->add($request->user('web'), $productId);
        $wishlist->load(['product.category', 'product.media']);
        $count = $this->wishlistService->count($request->user('web'));

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Product saved to your wishlist.',
                'wishlist' => ['count' => $count, 'product_id' => $productId, 'active' => true],
                'card_html' => view('website.account.wishlist.partials.card', compact('wishlist'))->render(),
                'in_stock' => (bool) ($wishlist->product && $wishlist->product->status === 'active' && (int) $wishlist->product->stock_quantity > 0),
            ]);
        }

        return back()->with('success', 'Product saved to your wishlist.');
    }

    public function destroy(Request $request, int $productId): JsonResponse|RedirectResponse
    {
        $this->wishlistService->remove($request->user('web'), $productId);
        $count = $this->wishlistService->count($request->user('web'));

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Product removed from your wishlist.',
                'wishlist' => ['count' => $count, 'product_id' => $productId, 'active' => false],
            ]);
        }

        return back()->with('success', 'Product removed from your wishlist.');
    }

    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $this->wishlistService->clear($request->user('web'));

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Your wishlist has been cleared.',
                'wishlist' => ['count' => 0],
            ]);
        }

        return back()->with('success', 'Your wishlist has been cleared.');
    }

    public function addAllToCart(Request $request): JsonResponse|RedirectResponse
    {
        $result = $this->wishlistService->addAvailableToCart($request->user('web'), $this->cartService);
        $message = $result['added'] > 0
            ? "{$result['added']} wishlist item(s) added to your cart."
            : 'No additional wishlist items could be added to your cart.';

        $summary = $this->cartService->summary();

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cart' => [
                    'count' => (int) $summary['count'],
                    'grand_total' => (float) $summary['grandTotal'],
                ],
                'result' => $result,
            ]);
        }

        return back()->with('success', $message);
    }

    private function wantsJson(Request $request): bool
    {
        return $request->ajax() || $request->expectsJson();
    }
}
