<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;

class VendorProductPolicy
{
    public function viewAny(User $user, Vendor $vendor): bool
    {
        return $this->hasMembership($user, $vendor->id);
    }

    public function create(User $user): bool
    {
        return $user->vendors()
            ->wherePivot('status', 'active')
            ->wherePivot('role', 'owner')
            ->where('vendors.status', Vendor::STATUS_APPROVED)
            ->count() === 1;
    }

    public function view(User $user, Product $product): bool
    {
        return $product->vendor_id !== null && $this->hasMembership($user, (int) $product->vendor_id);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }

    private function hasMembership(User $user, int $vendorId): bool
    {
        return $user->vendors()
            ->whereKey($vendorId)
            ->wherePivot('status', 'active')
            ->wherePivot('role', 'owner')
            ->where('vendors.status', Vendor::STATUS_APPROVED)
            ->exists();
    }
}
