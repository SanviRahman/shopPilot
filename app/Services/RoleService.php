<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            $name = Str::lower(trim($data['name']));
            $permissionIds = $data['permissions'] ?? [];

            $role = Role::withTrashed()
                ->where('name', $name)
                ->where('guard_name', 'admin')
                ->first();

            if (! $role) {
                $role = Role::create(['name' => $name, 'guard_name' => 'admin']);
            } elseif ($role->trashed()) {
                $role->restore();
            } else {
                throw ValidationException::withMessages(['name' => 'This role already exists.']);
            }

            $this->syncPermissions($role, $permissionIds);

            return $role->refresh()->load('permissions');
        });
    }

    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data): Role {
            $this->ensureCanModify($role);
            $name = Str::lower(trim($data['name']));

            $duplicate = Role::withTrashed()
                ->where('name', $name)
                ->where('guard_name', 'admin')
                ->where($role->getKeyName(), '!=', $role->getKey())
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages(['name' => 'This role already exists.']);
            }

            $role->update(['name' => $name]);
            $this->syncPermissions($role, $data['permissions'] ?? []);

            return $role->refresh()->load('permissions');
        });
    }

    public function delete(Role $role): void
    {
        $this->ensureCanDelete($role);
        $role->delete();
    }

    public function restore(Role $role): void
    {
        $role->restore();
    }

    public function forceDelete(Role $role): void
    {
        $this->ensureCanDelete($role);
        $role->syncPermissions([]);
        $role->forceDelete();
    }

    /** @return array{processed:int, skipped:int} */
    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $role = Role::withTrashed()->find($id);

                if (! $role) {
                    $skipped++;
                    continue;
                }

                try {
                    match ($action) {
                        'delete' => $this->delete($role),
                        'restore' => $role->trashed() ? $this->restore($role) : null,
                        'force-delete' => $role->trashed() ? $this->forceDelete($role) : null,
                        default => throw ValidationException::withMessages(['action' => 'Invalid bulk action.']),
                    };
                    $processed++;
                } catch (AuthorizationException) {
                    $skipped++;
                }
            }

            return compact('processed', 'skipped');
        });
    }

    private function syncPermissions(Role $role, array $permissionIds): void
    {
        $ids = array_unique(array_map('intval', $permissionIds));
        $permissions = \App\Models\Permission::query()
            ->where('guard_name', 'admin')
            ->whereIn('id', $ids)
            ->get();

        if ($permissions->count() !== count($ids)) {
            throw ValidationException::withMessages(['permissions' => 'One or more selected permissions are invalid.']);
        }

        $role->syncPermissions($permissions);
    }

    private function ensureCanModify(Role $role): void
    {
        if ($role->name === 'super_admin' && ! auth('admin')->user()?->isSuperAdmin()) {
            throw new AuthorizationException('The super_admin role is protected.');
        }
    }

    private function ensureCanDelete(Role $role): void
    {
        if ($role->name === 'super_admin') {
            throw new AuthorizationException('The super_admin role is protected.');
        }
    }
}
