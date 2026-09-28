<?php

namespace App\Services;

use App\Models\PaymentSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentSubmissionService
{
    public function verify(PaymentSubmission $paymentSubmission): PaymentSubmission
    {
        return DB::transaction(function () use ($paymentSubmission) {
            if ($paymentSubmission->status === 'verified') {
                throw ValidationException::withMessages([
                    'status' => 'This payment is already verified.',
                ]);
            }

            $paymentSubmission->update([
                'status' => 'verified',
                'verified_by_admin_id' => auth('admin')->id(),
                'verified_at' => now(),
                'rejection_note' => null,
            ]);

            $paymentSubmission->order()->update(['payment_status' => 'verified']);

            return $paymentSubmission->load(['order', 'paymentMethod', 'verifier']);
        });
    }

    public function reject(PaymentSubmission $paymentSubmission, string $note): PaymentSubmission
    {
        return DB::transaction(function () use ($paymentSubmission, $note) {
            $paymentSubmission->update([
                'status' => 'rejected',
                'verified_by_admin_id' => auth('admin')->id(),
                'verified_at' => now(),
                'rejection_note' => $note,
            ]);

            $paymentSubmission->order()->update(['payment_status' => 'rejected']);

            return $paymentSubmission->load(['order', 'paymentMethod', 'verifier']);
        });
    }

    public function delete(PaymentSubmission $paymentSubmission): void
    {
        $paymentSubmission->delete();
    }

    public function restore(PaymentSubmission $paymentSubmission): void
    {
        $paymentSubmission->restore();
    }

    public function forceDelete(PaymentSubmission $paymentSubmission): void
    {
        $paymentSubmission->forceDelete();
    }

    /** @return array{processed:int, skipped:int} */
    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $payment = PaymentSubmission::withTrashed()->find($id);

                if (! $payment) {
                    $skipped++;
                    continue;
                }

                match ($action) {
                    'delete' => $payment->delete(),
                    'restore' => $payment->restore(),
                    'force-delete' => $payment->forceDelete(),
                    default => null,
                };
                $processed++;
            }

            return compact('processed', 'skipped');
        });
    }
}