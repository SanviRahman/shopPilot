<?php

namespace App\Services\Website;

use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TrackOrderService
{
    public function find(string $orderNumber, string $contact): Order
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->with([
                'items.product.media',
                'paymentSubmission.paymentMethod',
                'histories' => fn ($query) => $query->orderBy('created_at'),
            ])
            ->first();

        if (! $order || ! $this->contactMatches($order, $contact)) {
            throw ValidationException::withMessages([
                'order_number' => 'We could not find an order matching those details.',
            ]);
        }

        return $order;
    }

    public function timeline(Order $order): array
    {
        if ($order->order_status === Order::STATUS_CANCELLED) {
            $cancelledAt = $order->histories
                ->firstWhere('to_status', Order::STATUS_CANCELLED)?->created_at
                ?? $order->updated_at;

            return [
                [
                    'key' => 'placed',
                    'label' => 'Order Placed',
                    'description' => 'Your order was received by ShopPilot.',
                    'completed' => true,
                    'current' => false,
                    'date' => $order->created_at,
                    'icon' => 'fa-shopping-bag',
                ],
                [
                    'key' => 'cancelled',
                    'label' => 'Order Cancelled',
                    'description' => 'This order is no longer being processed.',
                    'completed' => true,
                    'current' => true,
                    'date' => $cancelledAt,
                    'icon' => 'fa-times',
                    'danger' => true,
                ],
            ];
        }

        $statuses = [
            Order::STATUS_PENDING => ['Order Placed', 'Your order was received by ShopPilot.', 'fa-shopping-bag'],
            Order::STATUS_CONFIRMED => ['Confirmed', 'Your order has been confirmed.', 'fa-check'],
            Order::STATUS_PROCESSING => ['Processing', 'Your items are being prepared.', 'fa-box-open'],
            Order::STATUS_SHIPPED => ['Shipped', 'Your order is on the way.', 'fa-shipping-fast'],
            Order::STATUS_DELIVERED => ['Delivered', 'Your order has been delivered.', 'fa-home'],
        ];

        $keys = array_keys($statuses);
        $currentIndex = array_search($order->order_status, $keys, true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;

        return collect($statuses)->map(function (array $meta, string $status) use ($order, $keys, $currentIndex): array {
            $index = array_search($status, $keys, true);
            $history = $order->histories->firstWhere('to_status', $status);

            return [
                'key' => $status,
                'label' => $meta[0],
                'description' => $meta[1],
                'icon' => $meta[2],
                'completed' => $index <= $currentIndex,
                'current' => $index === $currentIndex,
                'date' => $status === Order::STATUS_PENDING
                    ? $order->created_at
                    : ($history?->created_at),
                'danger' => false,
            ];
        })->values()->all();
    }

    private function contactMatches(Order $order, string $contact): bool
    {
        $contact = trim($contact);
        $emailMatches = Str::lower($contact) === Str::lower((string) $order->buyer_email);

        $submittedPhone = $this->normalizePhone($contact);
        $orderPhone = $this->normalizePhone((string) $order->buyer_phone);

        $phoneMatches = $submittedPhone !== '' && $orderPhone !== '' && $submittedPhone === $orderPhone;

        return $emailMatches || $phoneMatches;
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';
        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 3);
        }
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return $digits;
    }
}
