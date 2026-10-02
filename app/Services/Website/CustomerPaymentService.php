<?php

namespace App\Services\Website;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentSubmission;
use App\Models\User;
use App\Services\OrderHistoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerPaymentService
{
    public function __construct(private readonly OrderHistoryService $orderHistoryService)
    {
    }

    public function submit(User $user, array $data): PaymentSubmission
    {
        return DB::transaction(function () use ($user, $data): PaymentSubmission {
            $order = Order::query()
                ->whereKey((int) $data['order_id'])
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($order->order_status, [Order::STATUS_CANCELLED, Order::STATUS_DELIVERED], true)) {
                throw ValidationException::withMessages([
                    'order_id' => 'Payment cannot be submitted for a cancelled or completed order.',
                ]);
            }

            if (! in_array($order->payment_status, [Order::PAYMENT_UNPAID, Order::PAYMENT_REJECTED], true)) {
                throw ValidationException::withMessages([
                    'order_id' => 'This order does not currently require a new payment submission.',
                ]);
            }

            $method = $this->activeManualPaymentMethod((int) $data['payment_method_id']);
            $payment = PaymentSubmission::withTrashed()->where('order_id', $order->id)->first();

            if ($payment && ! in_array($payment->status, ['rejected'], true)) {
                throw ValidationException::withMessages([
                    'order_id' => 'This order already has an active payment submission.',
                ]);
            }

            if ($payment?->trashed()) {
                $payment->restore();
            }

            $payload = [
                'order_id' => $order->id,
                'payment_method_id' => $method->id,
                'transaction_id' => $data['transaction_id'],
                'amount' => $order->grand_total,
                'status' => Order::PAYMENT_SUBMITTED,
                'verified_by_admin_id' => null,
                'verified_at' => null,
                'rejection_note' => null,
            ];

            if ($payment) {
                $payment->update($payload);
            } else {
                $payment = PaymentSubmission::create($payload);
            }

            $previousStatus = $order->payment_status;
            $order->update(['payment_status' => Order::PAYMENT_SUBMITTED]);

            $this->orderHistoryService->recordCustomerEvent(
                order: $order,
                userId: $user->id,
                note: "Payment submitted by customer. Payment status changed from {$previousStatus} to " . Order::PAYMENT_SUBMITTED . ". Transaction ID: {$payment->transaction_id}.",
            );

            return $payment->refresh()->load(['order', 'paymentMethod']);
        });
    }

    private function activeManualPaymentMethod(int $id): PaymentMethod
    {
        $codes = (array) config('shop.checkout.manual_payment_codes', []);

        $method = PaymentMethod::query()
            ->whereKey($id)
            ->where('status', 'active')
            ->whereIn('code', $codes)
            ->first();

        if (! $method) {
            throw ValidationException::withMessages([
                'payment_method_id' => 'Please select an active manual payment method.',
            ]);
        }

        return $method;
    }
}
