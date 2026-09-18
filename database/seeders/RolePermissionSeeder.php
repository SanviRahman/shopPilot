<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        $permissionGroups = [
            'Dashboard' => [
                'dashboard.view',
            ],
            'Users' => [
                'users.view',
                'users.update',
                'users.delete',
                'users.restore',
                'users.force-delete',
            ],
            'Customers' => [
                'customers.view',
            ],
            'Staff' => [
                'staff.view',
                'staff.create',
                'staff.update',
                'staff.delete',
                'staff.restore',
                'staff.force-delete',
            ],
            'Roles' => [
                'roles.view',
                'roles.manage',
            ],
            'Permissions' => [
                'permissions.view',
                'permissions.manage',
            ],
            'Categories' => [
                'categories.view',
                'categories.create',
                'categories.update',
                'categories.delete',
                'categories.restore',
                'categories.force-delete',
            ],
            'Products' => [
                'products.view',
                'products.create',
                'products.update',
                'products.delete',
                'products.restore',
                'products.force-delete',
            ],
            'Stock' => [
                'stock.view',
                'stock.update',
            ],
            'Coupons' => [
                'coupons.view',
                'coupons.create',
                'coupons.update',
                'coupons.delete',
                'coupons.restore',
                'coupons.force-delete',
            ],
            'Orders' => [
                'orders.view',
                'orders.update',
                'orders.assign',
                'orders.cancel',
                'orders.restore',
                'orders.force-delete',
            ],
            'Payments' => [
                'payments.view',
                'payments.verify',
                'payments.reject',
                'payments.restore',
                'payments.force-delete',
            ],
            'Payment Methods' => [
                'payment-methods.view',
                'payment-methods.manage',
                'payment-methods.delete',
                'payment-methods.restore',
                'payment-methods.force-delete',
            ],
            'Reports' => [
                'reports.view',
            ],
            'Settings' => [
                'settings.view',
                'settings.update',
            ],
        ];

        $allPermissionNames = [];

        foreach ($permissionGroups as $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::findOrCreate($permissionName, $guard);
                $allPermissionNames[] = $permissionName;
            }
        }

        $adminRole = Role::findOrCreate('Admin', $guard);
        $managerRole = Role::findOrCreate('Manager', $guard);
        $agentRole = Role::findOrCreate('Agent', $guard);
        $customerRole = Role::findOrCreate('Customer', $guard);

        $adminRole->syncPermissions($allPermissionNames);

        $managerRole->syncPermissions([
            'dashboard.view',
            'customers.view',
            'products.view',
            'products.create',
            'products.update',
            'stock.view',
            'stock.update',
            'orders.view',
            'orders.update',
            'orders.assign',
            'payments.view',
            'payments.verify',
            'reports.view',
        ]);

        $agentRole->syncPermissions([
            'dashboard.view',
            'orders.view',
            'orders.update',
            'orders.cancel',
        ]);

        $customerRole->syncPermissions([]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}