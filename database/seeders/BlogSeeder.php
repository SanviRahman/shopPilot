<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::role('super_admin')->first();
        $customer = User::role('customer')->first();

        if ($admin) {
            $blog = Blog::withTrashed()->updateOrCreate(
                ['slug' => 'welcome-to-shoppilot'],
                [
                    'admin_id' => $admin->id,
                    'user_id' => null,
                    'author_type' => Admin::class,
                    'author_id' => $admin->id,
                    'title' => 'Welcome to ShopPilot',
                    'content' => 'ShopPilot is ready to deliver a smooth, reliable and modern ecommerce experience.',
                ],
            );
            if ($blog->trashed()) $blog->restore();
        }

        if ($customer) {
            $blog = Blog::withTrashed()->updateOrCreate(
                ['slug' => 'customer-shopping-notes'],
                [
                    'admin_id' => null,
                    'user_id' => $customer->id,
                    'author_type' => User::class,
                    'author_id' => $customer->id,
                    'title' => 'Customer Shopping Notes',
                    'content' => 'A small seeded customer-authored blog entry for development and relationship testing.',
                ],
            );
            if ($blog->trashed()) $blog->restore();
        }
    }
}
