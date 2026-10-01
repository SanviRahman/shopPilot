<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentSubmissionService
{
    public function __construct(private readonly OrderHistoryService $orderHistoryService)
    {
    }

    public function verify(PaymentSubmission $paymentSubmission): PaymentSubmission
    {
        return DB::transaction(function () use ($paymentSubmission): PaymentSubmission {
            $this->ensureSubmitted($paymentSubmission);

            $order = $paymentSubmission->order()->firstOrFail();
            $previousPaymentStatus = $order->payment_status;

            $paymentSubmission->update([
                'status' => 'verified',
                'verified_by_admin_id' => auth('admin')->id(),
                'verified_at' => now(),
                'rejection_note' => null,
            ]);

            $order->update(['payment_status' => Order::PAYMENT_VERIFIED]);

            $this->orderHistoryService->recordAdminEvent(
                order: $order,
                note: "Payment verified. Payment status changed from {$previousPaymentStatus} to " . Order::PAYMENT_VERIFIED . ". Transaction ID: {$paymentSubmission->transaction_id}.",
            );

            return $paymentSubmission->refresh()->load(['order', 'paymentMethod', 'verifier']);
        });
    }

    public function reject(PaymentSubmission $paymentSubmission, string $note): PaymentSubmission
    {
        return DB::transaction(function () use ($paymentSubmission, $note): PaymentSubmission {
            $this->ensureSubmitted($paymentSubmission);

            $order = $paymentSubmission->order()->firstOrFail();
            $previousPaymentStatus = $order->payment_status;

            $paymentSubmission->update([
                'status' => 'rejected',
                'verified_by_admin_id' => auth('admin')->id(),
                'verified_at' => now(),
                'rejection_note' => trim($note),
            ]);

            $order->update(['payment_status' => Order::PAYMENT_REJECTED]);

            $this->orderHistoryService->recordAdminEvent(
                order: $order,
                note: "Payment rejected. Payment status changed from {$previousPaymentStatus} to " . Order::PAYMENT_REJECTED . ". Transaction ID: {$paymentSubmission->transaction_id}.",
            );

            return $paymentSubmission->refresh()->load(['order', 'paymentMethod', 'verifier']);
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

    private function ensureSubmitted(PaymentSubmission $paymentSubmission): void
    {
        if ($paymentSubmission->status !== 'submitted') {
            throw ValidationException::withMessages([
                'status' => 'Only a submitted payment can be verified or rejected.',
            ]);
        }
    }
}
