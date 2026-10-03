<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Order;

class OrderPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('orders.view');
    }

    public function view(Admin $admin, Order $order): bool
    {
        return $admin->can('orders.view') && $this->canAccessOrder($admin, $order);
    }

    public function update(Admin $admin, Order $order): bool
    {
        return $admin->can('orders.update') && $this->canAccessOrder($admin, $order);
    }

    public function delete(Admin $admin, Order $order): bool
    {
        return $admin->can('orders.delete') && $this->canAccessOrder($admin, $order);
    }

    public function restore(Admin $admin, Order $order): bool
    {
        return $admin->can('orders.restore') && $this->canAccessOrder($admin, $order);
    }

    public function forceDelete(Admin $admin, Order $order): bool
    {
        return $admin->can('orders.force-delete') && $this->canAccessOrder($admin, $order);
    }

    public function cancel(Admin $admin, Order $order): bool
    {
        return $admin->can('orders.cancel') && $this->canAccessOrder($admin, $order);
    }

    private function canAccessOrder(Admin $admin, Order $order): bool
    {
        if (! $admin->isRestrictedAgent()) {
            return true;
        }

        return (int) $order->assigned_agent_id === (int) $admin->getKey();
    }
}
