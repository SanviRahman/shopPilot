<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\PaymentSubmission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly OrderHistoryService $orderHistoryService)
    {
    }

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $currentAdmin = auth('admin')->user();

            if (! empty($data['assigned_agent_id'])) {
                if (! $currentAdmin?->can('orders.assign')) {
                    throw new AuthorizationException('You are not authorized to assign agents to orders.');
                }

                $this->ensureAssignableAgent((int) $data['assigned_agent_id']);
            }

            do {
                $orderNumber = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            } while (Order::withTrashed()->where('order_number', $orderNumber)->exists());

            $data['order_number'] = $orderNumber;
            $data['order_status'] = Order::STATUS_PENDING;
            $data['payment_status'] = Order::PAYMENT_UNPAID;

            $this->applyCalculatedTotals($data);

            $order = Order::create($data);

            $this->orderHistoryService->recordAdminEvent(
                order: $order,
                fromStatus: null,
                toStatus: Order::STATUS_PENDING,
                note: 'Order created.',
            );

            if ($order->assigned_agent_id !== null) {
                $agent = Admin::withTrashed()->find($order->assigned_agent_id);

                $this->orderHistoryService->recordAdminEvent(
                    order: $order,
                    note: 'Assigned to agent: ' . ($agent?->name ?? "Admin #{$order->assigned_agent_id}") . '.',
                );
            }

            return $order->load(['user', 'assignedAgent', 'coupon', 'paymentSubmission']);
        });
    }

    public function update(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data): Order {
            $currentAdmin = auth('admin')->user();
            $requestedStatus = (string) ($data['order_status'] ?? $order->order_status);
            $previousStatus = $order->order_status;
            $previousAgentId = $order->assigned_agent_id;

            if ($requestedStatus !== $order->order_status) {
                if (! $order->canTransitionTo($requestedStatus)) {
                    throw ValidationException::withMessages([
                        'order_status' => "Invalid order status transition from {$order->order_status} to {$requestedStatus}.",
                    ]);
                }

                if ($requestedStatus === Order::STATUS_CANCELLED && ! $currentAdmin?->can('orders.cancel')) {
                    throw new AuthorizationException('You are not authorized to cancel this order.');
                }
            }

            if (array_key_exists('assigned_agent_id', $data)) {
                $newAgentId = ! empty($data['assigned_agent_id']) ? (int) $data['assigned_agent_id'] : null;

                if ($newAgentId !== $order->assigned_agent_id) {
                    if (! $currentAdmin?->can('orders.assign')) {
                        throw new AuthorizationException('You are not authorized to assign agents to orders.');
                    }

                    if ($newAgentId !== null) {
                        $this->ensureAssignableAgent($newAgentId);
                    }
                }
            }

            // Payment state is owned by the payment workflow, never by the general Order form.
            unset($data['payment_status']);

            $this->applyCalculatedTotals($data, $order);
            $order->update($data);
            $order->refresh();

            if ($order->assigned_agent_id !== $previousAgentId) {
                $this->orderHistoryService->recordAdminEvent(
                    order: $order,
                    note: $this->assignmentHistoryNote($previousAgentId, $order->assigned_agent_id),
                );
            }

            if ($requestedStatus !== $previousStatus) {
                $this->orderHistoryService->recordAdminEvent(
                    order: $order,
                    fromStatus: $previousStatus,
                    toStatus: $requestedStatus,
                    note: "Order status changed from {$previousStatus} to {$requestedStatus}.",
                );
            }

            return $order->load(['user', 'assignedAgent', 'coupon', 'paymentSubmission']);
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
        $hasItems = OrderItem::withTrashed()->where('order_id', $order->id)->exists();
        $hasHistories = OrderHistory::withTrashed()->where('order_id', $order->id)->exists();
        $hasPayment = PaymentSubmission::withTrashed()->where('order_id', $order->id)->exists();

        if ($hasItems || $hasHistories || $hasPayment) {
            throw ValidationException::withMessages([
                'order' => 'This order has related items, history, or payment records and cannot be permanently deleted.',
            ]);
        }

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
                        'delete' => $this->delete($order),
                        'restore' => $this->restoreTrashed($order),
                        'force-delete' => $this->forceDeleteTrashed($order),
                        default => throw ValidationException::withMessages([
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

    private function ensureAssignableAgent(int $adminId): void
    {
        $isAssignableAgent = Admin::query()
            ->whereKey($adminId)
            ->where('status', 'active')
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'agent')->where('guard_name', 'admin');
            })
            ->exists();

        if (! $isAssignableAgent) {
            throw ValidationException::withMessages([
                'assigned_agent_id' => 'The selected account is not an active Agent.',
            ]);
        }
    }

    private function assignmentHistoryNote(?int $previousAgentId, ?int $newAgentId): string
    {
        $previousAgent = $previousAgentId !== null ? Admin::withTrashed()->find($previousAgentId) : null;
        $newAgent = $newAgentId !== null ? Admin::withTrashed()->find($newAgentId) : null;

        if ($previousAgentId === null && $newAgentId !== null) {
            return 'Assigned to agent: ' . ($newAgent?->name ?? "Admin #{$newAgentId}") . '.';
        }

        if ($previousAgentId !== null && $newAgentId === null) {
            return 'Agent assignment removed. Previous agent: ' . ($previousAgent?->name ?? "Admin #{$previousAgentId}") . '.';
        }

        return 'Reassigned from '
            . ($previousAgent?->name ?? "Admin #{$previousAgentId}")
            . ' to '
            . ($newAgent?->name ?? "Admin #{$newAgentId}")
            . '.';
    }

    private function applyCalculatedTotals(array &$data, ?Order $order = null): void
    {
        $subtotal = array_key_exists('subtotal', $data)
            ? (float) $data['subtotal']
            : (float) ($order?->subtotal ?? 0);

        $discount = array_key_exists('discount', $data)
            ? (float) ($data['discount'] ?? 0)
            : (float) ($order?->discount ?? 0);

        $shipping = array_key_exists('shipping', $data)
            ? (float) ($data['shipping'] ?? 0)
            : (float) ($order?->shipping ?? 0);

        if ($discount > $subtotal) {
            throw ValidationException::withMessages([
                'discount' => 'Discount cannot be greater than the order subtotal.',
            ]);
        }

        $data['subtotal'] = round($subtotal, 2);
        $data['discount'] = round($discount, 2);
        $data['shipping'] = round($shipping, 2);
        $data['grand_total'] = round(($subtotal - $discount) + $shipping, 2);
    }

    private function restoreTrashed(Order $order): void
    {
        if ($order->trashed()) {
            $this->restore($order);
        }
    }

    private function forceDeleteTrashed(Order $order): void
    {
        if ($order->trashed()) {
            $this->forceDelete($order);
        }
    }
}
