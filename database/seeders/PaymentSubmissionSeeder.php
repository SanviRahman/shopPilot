<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentSubmission;
use Illuminate\Database\Seeder;

class PaymentSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $verifier = Admin::role('super_admin')->first();
        $bkash = PaymentMethod::where('code', 'bkash')->firstOrFail();
        $nagad = PaymentMethod::where('code', 'nagad')->firstOrFail();

        $rows = [
            'SP-DEMO-0001' => ['method' => $bkash, 'transaction_id' => 'BK-DEMO-0001', 'status' => 'verified'],
            'SP-DEMO-0002' => ['method' => $nagad, 'transaction_id' => 'NG-DEMO-0002', 'status' => 'submitted'],
            'SP-DEMO-0003' => ['method' => $bkash, 'transaction_id' => 'BK-DEMO-0003', 'status' => 'verified'],
            'SP-DEMO-0005' => ['method' => $nagad, 'transaction_id' => 'NG-DEMO-0005', 'status' => 'rejected'],
        ];

        foreach ($rows as $orderNumber => $data) {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();
            $status = $data['status'];

            $submission = PaymentSubmission::withTrashed()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_method_id' => $data['method']->id,
                    'transaction_id' => $data['transaction_id'],
                    'amount' => $order->grand_total,
                    'status' => $status,
                    'verified_by_admin_id' => $status === 'verified' ? $verifier?->id : null,
                    'verified_at' => $status === 'verified' ? now() : null,
                    'rejection_note' => $status === 'rejected' ? 'Seeded rejected payment for workflow testing.' : null,
                ],
            );

            if ($submission->trashed()) $submission->restore();
        }
    }
}
