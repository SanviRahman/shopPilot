<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            BlogSeeder::class,
            CouponSeeder::class,
            PaymentMethodSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            OrderHistorySeeder::class,
            PaymentSubmissionSeeder::class,
            MediaSeeder::class,
        ]);
    }
}
