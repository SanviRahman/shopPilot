<?php
namespace App\Services;

use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderHistoryService
{
    public function create(array $data): OrderHistory
    {
        return DB::transaction(function () use ($data): OrderHistory {
            $order = Order::findOrFail($data['order_id']);

            // Dual Actor Rule: Admin event -> admin_id populated, user_id NULL
            $data['admin_id']    = auth('admin')->id();
            $data['user_id']     = null;
            $data['from_status'] = $order->order_status;

            // If to_status is provided and changed, validate the canonical order transition first.
            if (! empty($data['to_status']) && $data['to_status'] !== $order->order_status) {
                if (! $order->canTransitionTo($data['to_status'])) {
                    throw ValidationException::withMessages([
                        'to_status' => "Invalid order status transition from {$order->order_status} to {$data['to_status']}.",
                    ]);
                }

                if ($data['to_status'] === Order::STATUS_CANCELLED && ! auth('admin')->user()?->can('orders.cancel')) {
                    throw ValidationException::withMessages([
                        'to_status' => 'You are not authorized to cancel this order.',
                    ]);
                }

                $order->update(['order_status' => $data['to_status']]);
            } else {
                $data['to_status'] = $order->order_status;
            }

            $history = OrderHistory::create($data);

            return $history->load(['order', 'admin', 'user']);
        });
    }

    public function update(OrderHistory $history, array $data): OrderHistory
    {
        return DB::transaction(function () use ($history, $data): OrderHistory {
            $updateData = [
                'note' => $data['note'],
            ];

            // If to_status is updated, validate against the parent Order's current state.
            if (! empty($data['to_status']) && $data['to_status'] !== $history->to_status) {
                $order = $history->order;

                if ($order && $data['to_status'] !== $order->order_status) {
                    if (! $order->canTransitionTo($data['to_status'])) {
                        throw ValidationException::withMessages([
                            'to_status' => "Invalid order status transition from {$order->order_status} to {$data['to_status']}.",
                        ]);
                    }

                    if ($data['to_status'] === Order::STATUS_CANCELLED && ! auth('admin')->user()?->can('orders.cancel')) {
                        throw ValidationException::withMessages([
                            'to_status' => 'You are not authorized to cancel this order.',
                        ]);
                    }

                    $order->update(['order_status' => $data['to_status']]);
                }

                $updateData['to_status'] = $data['to_status'];
            }

            $history->update($updateData);

            return $history->refresh()->load(['order', 'admin', 'user']);
        });
    }
    public function delete(OrderHistory $history): void
    {
        $history->delete();
    }

    public function restore(OrderHistory $history): void
    {
        $history->restore();
    }

    public function forceDelete(OrderHistory $history): void
    {
        $history->forceDelete();
    }

    /** @return array{processed:int, skipped:int} */
    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped   = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $history = OrderHistory::withTrashed()->find($id);

                if (! $history) {
                    $skipped++;
                    continue;
                }

                try {
                    match ($action) {
                        'delete'       => $history->delete(),
                        'restore'      => $history->restore(),
                        'force-delete' => $history->forceDelete(),
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
}
