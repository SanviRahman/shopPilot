<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminService
{
    public function create(array $data): Admin
    {
        return DB::transaction(function () use ($data): Admin {
            $roleIds = $data['roles'];
            unset($data['roles']);

            $admin = Admin::create($data);
            $this->syncRoles($admin, $roleIds);

            return $admin->load('roles');
        });
    }

    public function update(Admin $admin, array $data): Admin
    {
        return DB::transaction(function () use ($admin, $data): Admin {
            $this->ensureCanModify($admin);
            $roleIds = $data['roles'];
            unset($data['roles']);

            if (blank($data['password'] ?? null)) {
                unset($data['password']);
            }

            $this->ensureSuperAdminAssignmentIsAuthorized($roleIds);

            $admin->update($data);
            $this->syncRoles($admin, $roleIds);

            return $admin->refresh()->load('roles');
        });
    }

    public function delete(Admin $admin): void
    {
        $this->ensureCanDelete($admin);
        $admin->delete();
    }

    public function restore(Admin $admin): void
    {
        $admin->restore();
    }

    public function forceDelete(Admin $admin): void
    {
        $this->ensureCanDelete($admin);
        $admin->syncRoles([]);
        $admin->syncPermissions([]);
        $admin->forceDelete();
    }

    /** @return array{processed:int, skipped:int} */
    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $admin = Admin::withTrashed()->find($id);

                if (! $admin) {
                    $skipped++;
                    continue;
                }

                try {
                    match ($action) {
                        'delete' => $this->delete($admin),
                        'restore' => $this->restoreTrashed($admin),
                        'force-delete' => $this->forceDeleteTrashed($admin),
                        default => throw ValidationException::withMessages([
                            'action' => 'Invalid bulk action.',
                        ]),
                    };
                    $processed++;
                } catch (AuthorizationException) {
                    $skipped++;
                }
            }

            return compact('processed', 'skipped');
        });
    }

    private function restoreTrashed(Admin $admin): void
    {
        if (! $admin->trashed()) {
            return;
        }

        $this->restore($admin);
    }

    private function forceDeleteTrashed(Admin $admin): void
    {
        if (! $admin->trashed()) {
            return;
        }

        $this->forceDelete($admin);
    }

    private function syncRoles(Admin $admin, array $roleIds): void
    {
        $roles = Role::query()
            ->where('guard_name', 'admin')
            ->whereIn('id', array_unique(array_map('intval', $roleIds)))
            ->get();

        if ($roles->count() !== count(array_unique($roleIds))) {
            throw ValidationException::withMessages([
                'roles' => 'One or more selected roles are invalid.',
            ]);
        }

        $this->ensureSuperAdminAssignmentIsAuthorized($roles->modelKeys());
        $admin->syncRoles($roles);
    }

    private function ensureSuperAdminAssignmentIsAuthorized(array $roleIds): void
    {
        $containsSuperAdmin = Role::query()
            ->whereIn('id', array_unique(array_map('intval', $roleIds)))
            ->where('guard_name', 'admin')
            ->where('name', 'super_admin')
            ->exists();

        if ($containsSuperAdmin && ! auth('admin')->user()?->isSuperAdmin()) {
            throw new AuthorizationException('Only a super admin may assign the super_admin role.');
        }
    }

    private function ensureCanModify(Admin $admin): void
    {
        $currentAdmin = auth('admin')->user();

        if ($admin->isSuperAdmin() && ! $currentAdmin?->isSuperAdmin()) {
            throw new AuthorizationException('The super_admin account is protected.');
        }
    }

    private function ensureCanDelete(Admin $admin): void
    {
        $currentAdmin = auth('admin')->user();

        if ($currentAdmin?->is($admin)) {
            throw new AuthorizationException('You cannot delete your own admin account.');
        }

        if ($admin->isSuperAdmin()) {
            throw new AuthorizationException('The super_admin account is protected.');
        }
    }
}
