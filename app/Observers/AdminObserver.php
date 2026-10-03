<?php

namespace App\Observers;

use App\Models\Admin;

class AdminObserver
{
    /**
     * Create one deterministic default blog when a new admin account is created.
     */
    public function created(Admin $admin): void
    {
        $admin->blogs()->firstOrCreate(
            ['slug' => "admin-{$admin->id}-staff-blog"],
            [
                'author_type' => Admin::class,
                'author_id' => $admin->id,
                'title' => $admin->name . "'s Staff Blog",
                'description' => 'Staff profile journal for ShopPilot administration and internal platform updates.',
                'content' => 'Staff profile initialized. This is an automatically generated administrative blog post.',
            ],
        );
    }

    /**
     * Keep the admin-owned blogs aligned with an admin soft delete / force delete.
     */
    public function deleted(Admin $admin): void
    {
        if (method_exists($admin, 'isForceDeleting') && $admin->isForceDeleting()) {
            $admin->blogs()->withTrashed()->forceDelete();
            return;
        }

        $admin->blogs()->delete();
    }

    /**
     * Restore the admin-owned blogs when the admin account is restored.
     */
    public function restored(Admin $admin): void
    {
        $admin->blogs()->onlyTrashed()->restore();
    }
}
