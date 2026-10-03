<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function data(?Admin $viewer = null): array
    {
        $viewer ??= auth('admin')->user();

        $activeOrders = $this->orderQuery($viewer);
        $deliveredOrders = (clone $activeOrders)->where('order_status', Order::STATUS_DELIVERED);
        $productIds = $this->visibleProductIds($viewer);
        $products = Product::query();

        if ($viewer?->isRestrictedAgent()) {
            $products->whereIn('id', $productIds);
        }

        $customerCount = $viewer?->isRestrictedAgent()
            ? $this->orderQuery($viewer)->whereNotNull('user_id')->distinct()->count('user_id')
            : User::query()->count();

        return [
            'stats' => [
                'total_sales' => (float) (clone $deliveredOrders)->sum('grand_total'),
                'total_orders' => (clone $activeOrders)->count(),
                'pending_orders' => (clone $activeOrders)
                    ->whereIn('order_status', [
                        Order::STATUS_PENDING,
                        Order::STATUS_CONFIRMED,
                        Order::STATUS_PROCESSING,
                        Order::STATUS_SHIPPED,
                    ])->count(),
                'delivered_orders' => (clone $deliveredOrders)->count(),
                'total_products' => (clone $products)->count(),
                'active_products' => (clone $products)->where('status', 'active')->count(),
                'customers' => $customerCount,
                'low_stock' => (clone $products)
                    ->where('status', 'active')
                    ->where('stock_quantity', '<=', 5)
                    ->count(),
            ],
            'salesChart' => $this->monthlySales($viewer),
            'ordersChart' => $this->dailyOrders($viewer),
            'statusChart' => $this->orderStatusChart($viewer),
            'roleChart' => $this->roleWorkload($viewer),
            'staffWorkload' => $this->staffWorkload($viewer),
            'topProducts' => $this->topProducts($viewer),
            'recentOrders' => $this->orderQuery($viewer)
                ->with(['user', 'assignedAgent.roles'])
                ->latest()
                ->limit(8)
                ->get(),
            'recentOrderHistory' => OrderHistory::query()
                ->whereHas('order', fn (Builder $query) => $this->scopeOrderQuery($query, $viewer))
                ->with(['order', 'admin', 'user'])
                ->latest()
                ->limit(10)
                ->get(),
            'recentProducts' => Product::query()
                ->when(
                    $viewer?->isRestrictedAgent(),
                    fn (Builder $query) => $query->whereIn('id', $productIds),
                )
                ->with('category')
                ->latest('updated_at')
                ->limit(8)
                ->get(),
            'isRestrictedAgentDashboard' => (bool) $viewer?->isRestrictedAgent(),
        ];
    }

    private function monthlySales(?Admin $viewer): array
    {
        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $labels[] = $month->format('M Y');
            $values[] = (float) $this->orderQuery($viewer)
                ->where('order_status', Order::STATUS_DELIVERED)
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('grand_total');
        }

        return compact('labels', 'values');
    }

    private function dailyOrders(?Admin $viewer): array
    {
        $labels = [];
        $values = [];

        for ($i = 29; $i >= 0; $i--) {
            $day = now()->startOfDay()->subDays($i);
            $labels[] = $day->format('d M');
            $values[] = $this->orderQuery($viewer)
                ->whereBetween('created_at', [$day, $day->copy()->endOfDay()])
                ->count();
        }

        return compact('labels', 'values');
    }

    private function orderStatusChart(?Admin $viewer): array
    {
        $counts = $this->orderQuery($viewer)
            ->select('order_status', DB::raw('COUNT(*) as total'))
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return [
            'labels' => collect(Order::ORDER_STATUSES)->map(fn (string $status) => ucfirst($status))->all(),
            'values' => collect(Order::ORDER_STATUSES)->map(fn (string $status) => (int) ($counts[$status] ?? 0))->all(),
        ];
    }

    private function roleWorkload(?Admin $viewer): array
    {
        if ($viewer?->isRestrictedAgent()) {
            return [
                'labels' => ['AGENT'],
                'values' => [$this->orderQuery($viewer)->count()],
            ];
        }

        $roles = ['super_admin', 'admin', 'manager', 'agent'];
        $admins = Admin::query()->with('roles:id,name')->withCount('assignedOrders')->get();
        $values = collect($roles)->map(function (string $role) use ($admins): int {
            return (int) $admins
                ->filter(fn (Admin $admin) => $admin->roles->contains('name', $role))
                ->sum('assigned_orders_count');
        })->all();

        return [
            'labels' => array_map(fn ($role) => strtoupper(str_replace('_', ' ', $role)), $roles),
            'values' => $values,
        ];
    }

    private function staffWorkload(?Admin $viewer): Collection
    {
        return Admin::query()
            ->when($viewer?->isRestrictedAgent(), fn (Builder $query) => $query->whereKey($viewer->getKey()))
            ->with('roles:id,name')
            ->withCount([
                'assignedOrders',
                'assignedOrders as open_orders_count' => fn ($query) => $query->whereNotIn(
                    'order_status',
                    [Order::STATUS_DELIVERED, Order::STATUS_CANCELLED],
                ),
            ])
            ->orderByDesc('assigned_orders_count')
            ->limit(12)
            ->get();
    }

    private function topProducts(?Admin $viewer): Collection
    {
        return OrderItem::query()
            ->whereHas('order', fn (Builder $query) => $this->scopeOrderQuery($query, $viewer)
                ->where('order_status', '!=', Order::STATUS_CANCELLED))
            ->select('product_id', 'product_name', 'sku')
            ->selectRaw('SUM(quantity) as units_sold')
            ->selectRaw('SUM(line_total) as revenue')
            ->groupBy('product_id', 'product_name', 'sku')
            ->orderByDesc('units_sold')
            ->limit(8)
            ->get();
    }

    private function orderQuery(?Admin $viewer): Builder
    {
        return $this->scopeOrderQuery(Order::query(), $viewer);
    }

    private function scopeOrderQuery(Builder $query, ?Admin $viewer): Builder
    {
        if ($viewer?->isRestrictedAgent()) {
            $query->where('assigned_agent_id', $viewer->getKey());
        }

        return $query;
    }

    private function visibleProductIds(?Admin $viewer): Collection
    {
        if (! $viewer?->isRestrictedAgent()) {
            return collect();
        }

        return OrderItem::query()
            ->whereHas('order', fn (Builder $query) => $this->scopeOrderQuery($query, $viewer))
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id');
    }
}
