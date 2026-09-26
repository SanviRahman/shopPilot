<?php

namespace App\Services;

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentMethodService
{
    public function create(array $data): PaymentMethod
    {
        return DB::transaction(fn () => PaymentMethod::create($data));
    }

    public function update(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        return DB::transaction(function () use ($paymentMethod, $data) {
            $paymentMethod->update($data);
            return $paymentMethod->refresh();
        });
    }

    public function delete(PaymentMethod $paymentMethod): void
    {
        $paymentMethod->delete();
    }

    public function restore(PaymentMethod $paymentMethod): void
    {
        $paymentMethod->restore();
    }

    public function forceDelete(PaymentMethod $paymentMethod): void
    {
        $paymentMethod->forceDelete();
    }

    /** @return array{processed:int, skipped:int} */
    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $method = PaymentMethod::withTrashed()->find($id);

                if (! $method) {
                    $skipped++;
                    continue;
                }

                try {
                    match ($action) {
                        'delete' => $this->delete($method),
                        'restore' => $this->restoreTrashed($method),
                        'force-delete' => $this->forceDeleteTrashed($method),
                        default => throw ValidationException::withMessages([
                            'action' => 'Invalid bulk action.',
                        ]),
                    };
                    $processed++;
                } catch (\Throwable) {
                    $skipped++;
                }
            }

            return compact('processed', 'skipped');
        });
    }

    private function restoreTrashed(PaymentMethod $paymentMethod): void
    {
        if ($paymentMethod->trashed()) {
            $this->restore($paymentMethod);
        }
    }

    private function forceDeleteTrashed(PaymentMethod $paymentMethod): void
    {
        if ($paymentMethod->trashed()) {
            $this->forceDelete($paymentMethod);
        }
    }
}