<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PermissionService
{
    public function create(array $data): Permission
    {
        return DB::transaction(function () use ($data): Permission {
            $name = Str::lower(trim($data['name']));
            $permission = Permission::withTrashed()
                ->where('name', $name)
                ->where('guard_name', 'admin')
                ->first();

            if (! $permission) {
                $permission = Permission::create([
                    'name' => $name,
                    'guard_name' => 'admin',
                    'group_name' => $data['group_name'] ?? null,
                ]);
            } elseif ($permission->trashed()) {
                $permission->update(['group_name' => $data['group_name'] ?? null]);
                $permission->restore();
            } else {
                throw ValidationException::withMessages(['name' => 'This permission already exists.']);
            }

            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            return $permission->refresh();
        });
    }

    public function update(Permission $permission, array $data): Permission
    {
        return DB::transaction(function () use ($permission, $data): Permission {
            $name = Str::lower(trim($data['name']));
            $duplicate = Permission::withTrashed()
                ->where('name', $name)
                ->where('guard_name', 'admin')
                ->where($permission->getKeyName(), '!=', $permission->getKey())
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages(['name' => 'This permission already exists.']);
            }

            $permission->update([
                'name' => $name,
                'group_name' => $data['group_name'] ?? null,
            ]);
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            return $permission->refresh();
        });
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function restore(Permission $permission): void
    {
        $permission->restore();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function forceDelete(Permission $permission): void
    {
        $permission->syncRoles([]);
        $permission->forceDelete();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array{processed:int, skipped:int} */
    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $permission = Permission::withTrashed()->find($id);

                if (! $permission) {
                    $skipped++;
                    continue;
                }

                match ($action) {
                    'delete' => $this->delete($permission),
                    'restore' => $permission->trashed() ? $this->restore($permission) : null,
                    'force-delete' => $permission->trashed() ? $this->forceDelete($permission) : null,
                    default => throw ValidationException::withMessages(['action' => 'Invalid bulk action.']),
                };
                $processed++;
            }

            return compact('processed', 'skipped');
        });
    }
}
