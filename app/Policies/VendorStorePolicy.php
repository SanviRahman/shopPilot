<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;
use App\Models\Vendor;

class VendorStorePolicy
{
    public function update(User $user, Store $store): bool
    {
        return $user->vendors()
            ->whereKey($store->vendor_id)
            ->wherePivot('status', 'active')
            ->wherePivot('role', 'owner')
            ->where('vendors.status', Vendor::STATUS_APPROVED)
            ->exists();
    }
}
