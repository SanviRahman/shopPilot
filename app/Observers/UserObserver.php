<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->blogs()->create([
            'author_type' => User::class,
            'author_id'   => $user->id,
            'title'       => $user->name . "'s Welcome Blog",
            'slug'        => Str::slug($user->name . '-welcome-' . uniqid()),
            'content'     => 'Welcome to ShopPilot! This is an automatically generated blog post for your account.',
        ]);
    }

    /**
     * Handle the User "deleted" event (Cascade Soft/Force Delete).
     */
    public function deleted(User $user): void
    {
        if (method_exists($user, 'isForceDeleting') && $user->isForceDeleting()) {
            $user->blogs()->withTrashed()->forceDelete();
        } else {
            $user->blogs()->delete();
        }
    }

    /**
     * Handle the User "restored" event (Cascade Restore).
     */
    public function restored(User $user): void
    {
        $user->blogs()->onlyTrashed()->restore();
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $user->blogs()->withTrashed()->forceDelete();
    }
}