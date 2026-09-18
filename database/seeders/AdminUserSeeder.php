<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@shoppilot.test')],
            [
                'name' => env('ADMIN_NAME', 'ShopPilot Admin'),
                'password' => env('ADMIN_PASSWORD', 'password'),
            ]
        );

        $admin->syncRoles(['Admin']);
    }
}