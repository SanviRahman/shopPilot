<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Demo Customer', 'email' => 'customer@shoppilot.test'],
            ['name' => 'Nusrat Jahan', 'email' => 'nusrat@shoppilot.test'],
            ['name' => 'Rakib Hasan', 'email' => 'rakib@shoppilot.test'],
        ];

        foreach ($users as $data) {
            $user = User::withTrashed()->updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => 'password'],
            );

            if ($user->trashed()) {
                $user->restore();
            }

            $user->forceFill(['email_verified_at' => now()])->save();
            $user->syncRoles(['customer']);
        }
    }
}
