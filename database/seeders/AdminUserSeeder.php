<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::withTrashed()->where('email', env('ADMIN_EMAIL', 'admin@shoppilot.test'))->first();

        if (! $admin) {
            $admin = Admin::create([
                'name' => env('ADMIN_NAME', 'ShopPilot Admin'),
                'email' => env('ADMIN_EMAIL', 'admin@shoppilot.test'),
                'password' => env('ADMIN_PASSWORD', 'password'),
                'status' => 'active',
            ]);
        } else {
            $admin->restore();
        }

        $admin->syncRoles(['super_admin']);
    }
}
