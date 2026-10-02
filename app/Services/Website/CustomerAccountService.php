<?php

namespace App\Services\Website;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentSubmission;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerAccountService
{
    public function dashboardData(User $user): array
    {
        $base = $user->orders()->whereNull('orders.deleted_at');
        $recentOrders = (clone $base)
            ->with(['paymentSubmission.paymentMethod'])
            ->latest()
            ->limit(5)
            ->get();

        $latestOrder = $recentOrders->first();

        return [
            'totalOrders' => (clone $base)->count(),
            'pendingOrders' => (clone $base)->whereIn('order_status', [
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_PROCESSING,
                Order::STATUS_SHIPPED,
            ])->count(),
            'completedOrders' => (clone $base)->where('order_status', Order::STATUS_DELIVERED)->count(),
            'paymentNeedsAction' => (clone $base)->whereIn('payment_status', [
                Order::PAYMENT_UNPAID,
                Order::PAYMENT_REJECTED,
            ])->count(),
            'wishlistCount' => Wishlist::query()->where('user_id', $user->id)->whereHas('product')->count(),
            'recentOrders' => $recentOrders,
            'latestOrder' => $latestOrder,
        ];
    }

    public function ordersData(User $user, ?string $status = null, ?string $search = null): array
    {
        $status = in_array($status, Order::ORDER_STATUSES, true) ? $status : null;
        $search = trim((string) $search);

        $query = $user->orders()
            ->with(['items.product.media', 'paymentSubmission.paymentMethod'])
            ->latest();

        if ($status !== null) {
            $query->where('order_status', $status);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('buyer_name', 'like', '%' . $search . '%');
            });
        }

        return [
            'orders' => $query->paginate(8)->withQueryString(),
            'status' => $status,
            'search' => $search,
            'statusCounts' => collect(Order::ORDER_STATUSES)->mapWithKeys(
                fn (string $value) => [$value => $user->orders()->where('order_status', $value)->count()],
            ),
            'allCount' => $user->orders()->count(),
        ];
    }

    public function ownedOrder(User $user, string $orderNumber): Order
    {
        return $user->orders()
            ->where('order_number', $orderNumber)
            ->with([
                'items.product.media',
                'coupon',
                'paymentSubmission.paymentMethod',
                'histories' => fn ($query) => $query->orderBy('created_at'),
            ])
            ->firstOrFail();
    }

    public function paymentData(User $user, ?string $status = null): array
    {
        $allowedStatuses = ['submitted', 'verified', 'rejected'];
        $status = in_array($status, $allowedStatuses, true) ? $status : null;

        $query = PaymentSubmission::query()
            ->whereHas('order', fn ($orderQuery) => $orderQuery->where('user_id', $user->id))
            ->with(['order', 'paymentMethod'])
            ->latest();

        if ($status !== null) {
            $query->where('status', $status);
        }

        $eligibleOrders = $user->orders()
            ->whereIn('payment_status', [Order::PAYMENT_UNPAID, Order::PAYMENT_REJECTED])
            ->whereNotIn('order_status', [Order::STATUS_CANCELLED, Order::STATUS_DELIVERED])
            ->with('paymentSubmission')
            ->latest()
            ->get();

        $codes = (array) config('shop.checkout.manual_payment_codes', []);

        return [
            'payments' => $query->paginate(10)->withQueryString(),
            'status' => $status,
            'statusCounts' => collect($allowedStatuses)->mapWithKeys(fn (string $value) => [
                $value => PaymentSubmission::query()
                    ->where('status', $value)
                    ->whereHas('order', fn ($orderQuery) => $orderQuery->where('user_id', $user->id))
                    ->count(),
            ]),
            'allCount' => PaymentSubmission::query()
                ->whereHas('order', fn ($orderQuery) => $orderQuery->where('user_id', $user->id))
                ->count(),
            'eligibleOrders' => $eligibleOrders,
            'paymentMethods' => PaymentMethod::query()
                ->where('status', 'active')
                ->whereIn('code', $codes)
                ->orderBy('id')
                ->get(),
        ];
    }
}
