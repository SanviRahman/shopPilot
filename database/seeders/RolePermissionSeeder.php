<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionGroups = [
            'Dashboard' => ['dashboard.view'],
            'Users' => ['users.view', 'users.update'],
            'Customers' => ['customers.view'],
            'Staff' => [
                'staff.view', 'staff.create', 'staff.update', 'staff.delete',
                'staff.restore', 'staff.force-delete',
            ],
            'Roles' => ['roles.view', 'roles.manage'],
            'Permissions' => ['permissions.view', 'permissions.manage'],
            'Categories' => [
                'categories.view', 'categories.create', 'categories.update',
                'categories.delete', 'categories.restore', 'categories.force-delete',
            ],
            'Media' => [
                'media.view', 'media.delete', 'media.restore', 'media.force-delete',
            ],
            'Products' => [
                'products.view', 'products.create', 'products.update',
                'products.delete', 'products.restore', 'products.force-delete',
            ],
            'Stock' => ['stock.view', 'stock.update'],
            'Coupons' => [
                'coupons.view', 'coupons.create', 'coupons.update',
                'coupons.delete', 'coupons.restore', 'coupons.force-delete',
            ],
            'Orders' => [
                'orders.view', 'orders.update', 'orders.assign', 'orders.cancel',
                'orders.restore', 'orders.force-delete',
            ],
            'Payments' => [
                'payments.view', 'payments.verify', 'payments.reject',
                'payments.restore', 'payments.force-delete',
            ],
            'Payment Methods' => [
                'payment-methods.view', 'payment-methods.manage',
                'payment-methods.delete', 'payment-methods.restore',
                'payment-methods.force-delete',
            ],
            'Reports' => ['reports.view'],
            'Settings' => ['settings.view', 'settings.update'],
        ];

        $allPermissionNames = [];

        foreach ($permissionGroups as $groupName => $permissions) {
            foreach ($permissions as $permissionName) {
                $permission = Permission::withTrashed()->firstOrNew([
                    'name' => $permissionName,
                    'guard_name' => 'admin',
                ]);
                $permission->group_name = $groupName;
                $permission->save();
                $permission->restore();
                $allPermissionNames[] = $permissionName;
            }
        }

        $superAdminRole = $this->findOrRestoreRole('super_admin', 'admin');
        $adminRole = $this->findOrRestoreRole('admin', 'admin');
        $managerRole = $this->findOrRestoreRole('manager', 'admin');
        $agentRole = $this->findOrRestoreRole('agent', 'admin');
        $this->findOrRestoreRole('customer', 'web');

        $superAdminRole->syncPermissions($allPermissionNames);
        $adminRole->syncPermissions($allPermissionNames);
        $managerRole->syncPermissions([
            'dashboard.view', 'customers.view', 'products.view', 'products.create',
            'products.update', 'stock.view', 'stock.update', 'orders.view',
            'orders.update', 'orders.assign', 'payments.view', 'payments.verify',
            'reports.view',
        ]);
        $agentRole->syncPermissions([
            'dashboard.view', 'orders.view', 'orders.update', 'orders.cancel',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function findOrRestoreRole(string $name, string $guard): Role
    {
        $role = Role::withTrashed()->firstOrNew([
            'name' => $name,
            'guard_name' => $guard,
        ]);
        $role->save();
        $role->restore();

        return $role;
    }
}
