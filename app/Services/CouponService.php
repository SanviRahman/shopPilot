<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

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
        return Coupon::create($this->normalizeData($data));
    }

    public function update(Coupon $coupon, array $data): bool
    {
        return $coupon->update($this->normalizeData($data));
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

    public function expirePastActiveCoupons(?Carbon $at = null): int
    {
        $at ??= now();

        return Coupon::query()
            ->where('status', Coupon::STATUS_ACTIVE)
            ->where('end_date', '<', $at)
            ->update(['status' => Coupon::STATUS_EXPIRED]);
    }

    /**
     * Resolve a coupon for checkout using the same server-authoritative rules that
     * the frontend will use later. The returned model is safe to calculate with.
     */
    public function resolveApplicableCoupon(string $code, float $orderAmount, ?Carbon $at = null): Coupon
    {
        $at ??= now();
        $normalizedCode = strtoupper(trim($code));

        $coupon = Coupon::query()
            ->where('code', $normalizedCode)
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Invalid coupon code.',
            ]);
        }

        if (! $coupon->isValidForOrderAmount($orderAmount, $at)) {
            throw ValidationException::withMessages([
                'coupon_code' => $this->invalidCouponMessage($coupon, $orderAmount, $at),
            ]);
        }

        return $coupon;
    }

    public function calculateDiscount(Coupon $coupon, float $orderAmount): float
    {
        return $coupon->calculateDiscount($orderAmount);
    }

    private function normalizeData(array $data): array
    {
        $endDate = Carbon::parse($data['end_date']);
        $status = $data['status'];

        if ($endDate->isPast() && $status === Coupon::STATUS_ACTIVE) {
            $status = Coupon::STATUS_EXPIRED;
        }

        return [
            'code' => strtoupper(trim($data['code'])),
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'minimum_order_amount' => $data['minimum_order_amount'] ?? 0,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $status,
        ];
    }

    private function invalidCouponMessage(Coupon $coupon, float $orderAmount, Carbon $at): string
    {
        if ($coupon->status !== Coupon::STATUS_ACTIVE) {
            return 'This coupon is not active.';
        }

        if ($coupon->start_date?->gt($at)) {
            return 'This coupon is not active yet.';
        }

        if ($coupon->end_date?->lt($at)) {
            return 'This coupon has expired.';
        }

        if ($orderAmount < (float) $coupon->minimum_order_amount) {
            return 'The minimum order amount for this coupon has not been reached.';
        }

        return 'This coupon cannot be applied.';
    }
}
