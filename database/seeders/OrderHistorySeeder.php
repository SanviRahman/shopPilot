<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Database\Seeder;

class OrderHistorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::role('super_admin')->first();
        $orders = Order::query()->get();

        foreach ($orders as $order) {
            $this->seedHistory($order, $admin?->id, null, null, Order::STATUS_PENDING, 'Order created.');

            $steps = match ($order->order_status) {
                Order::STATUS_CONFIRMED => [[Order::STATUS_PENDING, Order::STATUS_CONFIRMED]],
                Order::STATUS_PROCESSING => [[Order::STATUS_PENDING, Order::STATUS_CONFIRMED], [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING]],
                Order::STATUS_SHIPPED => [[Order::STATUS_PENDING, Order::STATUS_CONFIRMED], [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING], [Order::STATUS_PROCESSING, Order::STATUS_SHIPPED]],
                Order::STATUS_DELIVERED => [[Order::STATUS_PENDING, Order::STATUS_CONFIRMED], [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING], [Order::STATUS_PROCESSING, Order::STATUS_SHIPPED], [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]],
                Order::STATUS_CANCELLED => [[Order::STATUS_PENDING, Order::STATUS_CANCELLED]],
                default => [],
            };

            foreach ($steps as [$from, $to]) {
                $this->seedHistory($order, $admin?->id, null, $from, $to, 'Seeded status transition.');
            }
        }
    }

    private function seedHistory(Order $order, ?int $adminId, ?int $userId, ?string $from, ?string $to, string $note): void
    {
        $history = OrderHistory::withTrashed()->firstOrCreate([
            'order_id' => $order->id,
            'admin_id' => $adminId,
            'user_id' => $userId,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
        ]);

        if ($history->trashed()) $history->restore();
    }
}
