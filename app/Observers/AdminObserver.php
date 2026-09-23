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
            'title'   => $admin->name . "'s Staff Blog",
            'slug'    => Str::slug($admin->name . '-staff-' . uniqid()),
            'content' => 'Staff profile initialized. This is an automatically generated administrative blog post.',
        ]);
    }
}