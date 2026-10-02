<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function data(): array
    {
        $activeOrders = Order::query()->whereNull('deleted_at');
        $deliveredOrders = (clone $activeOrders)->where('order_status', Order::STATUS_DELIVERED);

        return [
            'stats' => [
                'total_sales' => (float) (clone $deliveredOrders)->sum('grand_total'),
                'total_orders' => (clone $activeOrders)->count(),
                'pending_orders' => (clone $activeOrders)->whereIn('order_status', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED])->count(),
                'delivered_orders' => (clone $deliveredOrders)->count(),
                'total_products' => Product::query()->count(),
                'active_products' => Product::query()->where('status', 'active')->count(),
                'customers' => User::query()->count(),
                'low_stock' => Product::query()->where('status', 'active')->where('stock_quantity', '<=', 5)->count(),
            ],
            'salesChart' => $this->monthlySales(),
            'ordersChart' => $this->dailyOrders(),
            'statusChart' => $this->orderStatusChart(),
            'roleChart' => $this->roleWorkload(),
            'staffWorkload' => $this->staffWorkload(),
            'topProducts' => $this->topProducts(),
            'recentOrders' => Order::query()->with(['user', 'assignedAgent.roles'])->latest()->limit(8)->get(),
            'recentOrderHistory' => OrderHistory::query()->with(['order', 'admin', 'user'])->latest()->limit(10)->get(),
            'recentProducts' => Product::query()->with('category')->latest('updated_at')->limit(8)->get(),
        ];
    }

    private function monthlySales(): array
    {
        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $labels[] = $month->format('M Y');
            $values[] = (float) Order::query()
                ->where('order_status', Order::STATUS_DELIVERED)
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('grand_total');
        }

        return compact('labels', 'values');
    }

    private function dailyOrders(): array
    {
        $labels = [];
        $values = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->startOfDay()->subDays($i);
            $labels[] = $day->format('d M');
            $values[] = Order::query()->whereBetween('created_at', [$day, $day->copy()->endOfDay()])->count();
        }
        return compact('labels', 'values');
    }

    private function orderStatusChart(): array
    {
        $counts = Order::query()
            ->select('order_status', DB::raw('COUNT(*) as total'))
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return [
            'labels' => collect(Order::ORDER_STATUSES)->map(fn (string $s) => ucfirst($s))->all(),
            'values' => collect(Order::ORDER_STATUSES)->map(fn (string $s) => (int) ($counts[$s] ?? 0))->all(),
        ];
    }

    private function roleWorkload(): array
    {
        $roles = ['super_admin', 'admin', 'manager', 'agent'];
        $admins = Admin::query()->with('roles:id,name')->withCount('assignedOrders')->get();
        $values = collect($roles)->map(function (string $role) use ($admins): int {
            return (int) $admins->filter(fn (Admin $admin) => $admin->roles->contains('name', $role))->sum('assigned_orders_count');
        })->all();

        return ['labels' => array_map(fn ($r) => strtoupper(str_replace('_', ' ', $r)), $roles), 'values' => $values];
    }

    private function staffWorkload(): Collection
    {
        return Admin::query()
            ->with('roles:id,name')
            ->withCount(['assignedOrders', 'assignedOrders as open_orders_count' => fn ($q) => $q->whereNotIn('order_status', [Order::STATUS_DELIVERED, Order::STATUS_CANCELLED])])
            ->orderByDesc('assigned_orders_count')
            ->limit(12)
            ->get();
    }

    private function topProducts(): Collection
    {
        return OrderItem::query()
            ->select('product_id', 'product_name', 'sku')
            ->selectRaw('SUM(quantity) as units_sold')
            ->selectRaw('SUM(line_total) as revenue')
            ->whereHas('order', fn ($q) => $q->where('order_status', '!=', Order::STATUS_CANCELLED))
            ->groupBy('product_id', 'product_name', 'sku')
            ->orderByDesc('units_sold')
            ->limit(8)
            ->get();
    }
}
