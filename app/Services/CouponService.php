<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CouponService
{
    public function getPaginatedCoupons(array $filters = [], bool $isTrash = false, int $perPage = 15): LengthAwarePaginator
    {
        $query = $isTrash ? Coupon::onlyTrashed() : Coupon::query();

        return $query
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $term = '%' . trim($filters['search']) . '%';
                $q->where('code', 'like', $term);
            })
            ->when(! empty($filters['status']), function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Coupon
    {
        return Coupon::create([
            'code' => strtoupper(trim($data['code'])),
            'type' => $data['type'],
            'value' => $data['value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => $data['status'],
        ]);
    }

    public function update(Coupon $coupon, array $data): bool
    {
        return $coupon->update([
            'code' => strtoupper(trim($data['code'])),
            'type' => $data['type'],
            'value' => $data['value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => $data['status'],
        ]);
    }

    public function delete(Coupon $coupon): bool
    {
        return (bool) $coupon->delete();
    }

    public function restore(int $id): bool
    {
        $coupon = Coupon::onlyTrashed()->findOrFail($id);
        return (bool) $coupon->restore();
    }

    public function forceDelete(int $id): bool
    {
        $coupon = Coupon::onlyTrashed()->findOrFail($id);
        return (bool) $coupon->forceDelete();
    }

    public function bulk(string $action, array $ids): array
    {
        $processed = 0;
        $uniqueIds = array_unique(array_map('intval', $ids));

        foreach ($uniqueIds as $id) {
            $coupon = match ($action) {
                'delete' => Coupon::find($id),
                'restore', 'force-delete' => Coupon::onlyTrashed()->find($id),
                default => null,
            };

            if (! $coupon) {
                continue;
            }

            match ($action) {
                'delete' => $coupon->delete(),
                'restore' => $coupon->restore(),
                'force-delete' => $coupon->forceDelete(),
            };

            $processed++;
        }

        return ['processed' => $processed];
    }
}