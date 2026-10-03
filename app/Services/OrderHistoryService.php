<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class OrderHistoryService
{
    /**
     * Add a manual staff audit note without mutating Order state.
     */
    public function addAdminNote(array $data): OrderHistory
    {
        $order = Order::query()->findOrFail((int) $data['order_id']);

        return $this->recordAdminEvent(
            order: $order,
            note: trim((string) $data['note']),
        );
    }

    public function recordAdminEvent(
        Order $order,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $note = null,
    ): OrderHistory {
        return $this->record(
            order: $order,
            adminId: auth('admin')->id(),
            userId: null,
            fromStatus: $fromStatus,
            toStatus: $toStatus,
            note: $note,
        );
    }

    public function recordCustomerEvent(
        Order $order,
        int $userId,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $note = null,
    ): OrderHistory {
        return $this->record(
            order: $order,
            adminId: null,
            userId: $userId,
            fromStatus: $fromStatus,
            toStatus: $toStatus,
            note: $note,
        );
    }

    public function recordSystemEvent(
        Order $order,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $note = null,
    ): OrderHistory {
        return $this->record(
            order: $order,
            adminId: null,
            userId: null,
            fromStatus: $fromStatus,
            toStatus: $toStatus,
            note: $note,
        );
    }

    /**
     * Central append-only history writer.
     * Exactly one actor type may be present; Guest/System uses both actor IDs as null.
     */
    public function record(
        Order $order,
        ?int $adminId,
        ?int $userId,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $note = null,
    ): OrderHistory {
        if ($adminId !== null && $userId !== null) {
            throw ValidationException::withMessages([
                'actor' => 'An order history event cannot have both an Admin actor and a User actor.',
            ]);
        }

        foreach (['from_status' => $fromStatus, 'to_status' => $toStatus] as $field => $status) {
            if ($status !== null && ! in_array($status, Order::ORDER_STATUSES, true)) {
                throw ValidationException::withMessages([
                    $field => "Invalid order status: {$status}.",
                ]);
            }
        }

        $normalizedNote = $note !== null ? trim($note) : null;

        if ($fromStatus === null && $toStatus === null && ($normalizedNote === null || $normalizedNote === '')) {
            throw ValidationException::withMessages([
                'note' => 'A history event must contain a status transition or a note.',
            ]);
        }

        return OrderHistory::create([
            'order_id' => $order->id,
            'admin_id' => $adminId,
            'user_id' => $userId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $normalizedNote !== '' ? $normalizedNote : null,
        ])->load(['order', 'admin', 'user']);
    }

    public function delete(OrderHistory $history): void
    {
        $history->delete();
    }

    public function restore(OrderHistory $history): void
    {
        if ($history->trashed()) {
            $history->restore();
        }
    }

    /** @return array{processed:int, skipped:int} */
    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $history = OrderHistory::withTrashed()->find($id);

                if (! $history) {
                    $skipped++;
                    continue;
                }

                $history->loadMissing('order');
                $ability = match ($action) {
                    'delete' => 'delete',
                    'restore' => 'restore',
                    default => throw ValidationException::withMessages([
                        'action' => 'Invalid bulk action.',
                    ]),
                };

                $admin = auth('admin')->user();
                if (! $admin || ! $history->order || Gate::forUser($admin)->denies($ability, $history->order)) {
                    $skipped++;
                    continue;
                }

                try {
                    match ($action) {
                        'delete' => $history->trashed() ? null : $this->delete($history),
                        'restore' => $history->trashed() ? $this->restore($history) : null,
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
