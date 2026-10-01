<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => env('ADMIN_NAME', 'ShopPilot Admin'),
                'email' => env('ADMIN_EMAIL', 'admin@shoppilot.test'),
                'password' => env('ADMIN_PASSWORD', 'password'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'ShopPilot Manager',
                'email' => 'manager@shoppilot.test',
                'password' => 'password',
                'role' => 'manager',
            ],
            [
                'name' => 'ShopPilot Agent',
                'email' => 'agent@shoppilot.test',
                'password' => 'password',
                'role' => 'agent',
            ],
        ];

        foreach ($accounts as $data) {
            $admin = Admin::withTrashed()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'status' => 'active',
                ],
            );

            if ($admin->trashed()) {
                $admin->restore();
            }

            $admin->forceFill(['email_verified_at' => now()])->save();
            $admin->syncRoles([$data['role']]);
        }
    }
}
