<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $customer = User::create($data);
            $this->ensureCustomerRole($customer);

            return $customer->loadCount('orders')->load('roles');
        });
    }

    public function update(User $customer, array $data): User
    {
        return DB::transaction(function () use ($customer, $data): User {
            if (blank($data['password'] ?? null)) {
                unset($data['password']);
            }

            $customer->update($data);
            $this->ensureCustomerRole($customer);

            return $customer->refresh()->loadCount('orders')->load('roles');
        });
    }

    public function delete(User $customer): void
    {
        $customer->delete();
    }

    public function restore(User $customer): void
    {
        $customer->restore();
        $this->ensureCustomerRole($customer);
    }

    public function forceDelete(User $customer): void
    {
        DB::transaction(function () use ($customer): void {
            $customer->syncRoles([]);
            $customer->syncPermissions([]);
            $customer->clearMediaCollection('avatar');
            $customer->clearMediaCollection('user_avatar');
            $customer->forceDelete();
        });
    }

    /** @return array{processed:int, skipped:int} */
    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $customer = User::withTrashed()->find($id);
                if (! $customer) {
                    $skipped++;
                    continue;
                }

                match ($action) {
                    'delete' => $customer->trashed() ? null : $this->delete($customer),
                    'restore' => $customer->trashed() ? $this->restore($customer) : null,
                    'force-delete' => $customer->trashed() ? $this->forceDelete($customer) : null,
                };
                $processed++;
            }

            return compact('processed', 'skipped');
        });
    }

    private function ensureCustomerRole(User $customer): void
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'customer')
            ->first();

        if ($role && ! $customer->hasRole($role)) {
            $customer->syncRoles([$role]);
        }
    }
}
