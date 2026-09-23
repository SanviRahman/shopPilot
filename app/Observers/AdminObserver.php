<?php

namespace App\Observers;

use App\Models\Admin;
use Illuminate\Support\Str;

class AdminObserver
{
    /**
     * Handle the Admin "created" event.
     */
    public function created(Admin $admin): void
    {
        $admin->blogs()->create([
            'author_type' => Admin::class,
            'author_id'   => $admin->id,
            'title'       => $admin->name . "'s Staff Blog",
            'slug'        => Str::slug($admin->name . '-staff-' . uniqid()),
            'content'     => 'Staff profile initialized. This is an automatically generated administrative blog post.',
        ]);
    }

    /**
     * Handle the Admin "deleted" event (Cascade Soft/Force Delete).
     */
    public function deleted(Admin $admin): void
    {
        if (method_exists($admin, 'isForceDeleting') && $admin->isForceDeleting()) {
            $admin->blogs()->withTrashed()->forceDelete();
        } else {
            $admin->blogs()->delete();
        }
    }

    /**
     * Handle the Admin "restored" event (Cascade Restore).
     */
    public function restored(Admin $admin): void
    {
        $admin->blogs()->onlyTrashed()->restore();
    }

    /**
     * Handle the Admin "force deleted" event.
     */
    public function forceDeleted(Admin $admin): void
    {
        $admin->blogs()->withTrashed()->forceDelete();
    }
}