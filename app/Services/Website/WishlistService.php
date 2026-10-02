<?php

namespace App\Services\Website;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\CartService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class WishlistService
{
    public function pageData(User $user): array
    {
        $items = Wishlist::query()
            ->where('user_id', $user->id)
            ->with(['product.category', 'product.media'])
            ->latest()
            ->get()
            ->filter(fn (Wishlist $wishlist) => $wishlist->product !== null)
            ->values();

        $productIds = $items->pluck('product_id')->map(fn ($id) => (int) $id)->all();

        $recommendedProducts = Product::query()
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->when($productIds !== [], fn ($query) => $query->whereNotIn('id', $productIds))
            ->with(['category', 'media'])
            ->orderByDesc('featured')
            ->latest('id')
            ->limit(4)
            ->get();

        return [
            'wishlistItems' => $items,
            'wishlistCount' => $items->count(),
            'wishlistInStockCount' => $items->filter(fn (Wishlist $item) => (int) $item->product->stock_quantity > 0)->count(),
            'wishlistOutOfStockCount' => $items->filter(fn (Wishlist $item) => (int) $item->product->stock_quantity <= 0)->count(),
            'recommendedProducts' => $recommendedProducts,
            'wishlistProductIds' => $productIds,
        ];
    }

    public function add(User $user, int $productId): Wishlist
    {
        $wishlist = Wishlist::withTrashed()->firstOrNew([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);

        if (! $wishlist->exists) {
            $wishlist->save();
        } elseif ($wishlist->trashed()) {
            $wishlist->restore();
        }

        return $wishlist;
    }

    public function remove(User $user, int $productId): bool
    {
        return Wishlist::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    public function clear(User $user): int
    {
        return Wishlist::query()->where('user_id', $user->id)->delete();
    }

    public function count(User $user): int
    {
        return Wishlist::query()
            ->where('user_id', $user->id)
            ->whereHas('product')
            ->count();
    }

    public function productIds(User $user): array
    {
        return Wishlist::query()
            ->where('user_id', $user->id)
            ->whereHas('product')
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function addAvailableToCart(User $user, CartService $cartService): array
    {
        $products = Wishlist::query()
            ->where('user_id', $user->id)
            ->with('product')
            ->get()
            ->pluck('product')
            ->filter(fn (?Product $product) => $product && $product->status === 'active' && (int) $product->stock_quantity > 0)
            ->values();

        $added = 0;
        $skipped = 0;

        foreach ($products as $product) {
            try {
                $cartService->add((int) $product->id, 1);
                $added++;
            } catch (ValidationException) {
                $skipped++;
            }
        }

        return [
            'added' => $added,
            'skipped' => $skipped,
        ];
    }
}
