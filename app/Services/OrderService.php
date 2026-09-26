<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $currentUser = auth('admin')->user();

            // Verify Agent Assignment authorization
            if (! empty($data['assigned_agent_id']) && ! $currentUser?->can('orders.assign')) {
                throw new AuthorizationException('You are not authorized to assign agents to orders.');
            }

            // Generate Unique Order Number: ORD-YYYYMMDD-XXXX
            do {
                $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            } while (Order::where('order_number', $orderNumber)->exists());

            $data['order_number'] = $orderNumber;

            // Strict Decimal Calculation for Grand Total
            $subtotal = (float) ($data['subtotal'] ?? 0);
            $discount = (float) ($data['discount'] ?? 0);
            $shipping = (float) ($data['shipping'] ?? 0);

            $data['discount'] = $discount;
            $data['shipping'] = $shipping;
            $data['grand_total'] = max(0, round($subtotal - $discount + $shipping, 2));

            $order = Order::create($data);

            return $order->load(['user', 'assignedAgent']);
        });
    }

    public function update(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data): Order {
            $currentUser = auth('admin')->user();

            // Check cancellation permission
            if (isset($data['order_status']) && $data['order_status'] === 'cancelled' && $order->order_status !== 'cancelled') {
                if (! $currentUser?->can('orders.cancel')) {
                    throw new AuthorizationException('You are not authorized to cancel this order.');
                }
            }

            // Check agent assignment permission
            if (array_key_exists('assigned_agent_id', $data)) {
                $newAgentId = ! empty($data['assigned_agent_id']) ? (int) $data['assigned_agent_id'] : null;
                if ($newAgentId !== $order->assigned_agent_id && ! $currentUser?->can('orders.assign')) {
                    throw new AuthorizationException('You are not authorized to assign agents to orders.');
                }
            }

            // Re-calculate Grand Total
            $subtotal = isset($data['subtotal']) ? (float) $data['subtotal'] : (float) $order->subtotal;
            $discount = isset($data['discount']) ? (float) $data['discount'] : (float) $order->discount;
            $shipping = isset($data['shipping']) ? (float) $data['shipping'] : (float) $order->shipping;

            $data['discount'] = $discount;
            $data['shipping'] = $shipping;
            $data['grand_total'] = max(0, round($subtotal - $discount + $shipping, 2));

            $order->update($data);

            return $order->refresh()->load(['user', 'assignedAgent']);
        });
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }

    public function restore(Order $order): void
    {
        $order->restore();
    }

    public function forceDelete(Order $order): void
    {
        $order->forceDelete();
    }

    /** @return array{processed:int, skipped:int} */
    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $order = Order::withTrashed()->find($id);

                if (! $order) {
                    $skipped++;
                    continue;
                }

                try {
                    match ($action) {
                        'delete'       => $this->delete($order),
                        'restore'      => $this->restoreTrashed($order),
                        'force-delete' => $this->forceDeleteTrashed($order),
                        default        => throw ValidationException::withMessages([
                            'action' => 'Invalid bulk action.',
                        ]),
                    };
                    $processed++;
                } catch (\Throwable) {
                    $skipped++;
                }
            }

            return compact('processed', 'skipped');
        });
    }

    private function restoreTrashed(Order $order): void
    {
        if (! $order->trashed()) {
            return;
        }

        $this->restore($order);
    }

    private function forceDeleteTrashed(Order $order): void
    {
        if (! $order->trashed()) {
            return;
        }

        $this->forceDelete($order);
    }
}