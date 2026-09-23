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
            'title'   => $user->name . "'s Welcome Blog",
            'slug'    => Str::slug($user->name . '-welcome-' . uniqid()),
            'content' => 'Welcome to ShopPilot! This is an automatically generated blog post for your account.',
        ]);
    }
}