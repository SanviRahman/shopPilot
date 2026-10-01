<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::role('customer')->orderBy('id')->get();
        $agent = Admin::role('agent')->where('status', 'active')->first();
        $welcome = Coupon::where('code', 'WELCOME10')->first();
        $save500 = Coupon::where('code', 'SAVE500')->first();

        $rows = [
            ['number' => 'SP-DEMO-0001', 'user' => $customers->get(0), 'coupon' => $welcome, 'status' => Order::STATUS_CONFIRMED, 'payment' => Order::PAYMENT_VERIFIED, 'shipping' => 80, 'city' => 'Dhaka', 'address' => 'Mirpur-2, Dhaka'],
            ['number' => 'SP-DEMO-0002', 'user' => $customers->get(1), 'coupon' => null, 'status' => Order::STATUS_PROCESSING, 'payment' => Order::PAYMENT_SUBMITTED, 'shipping' => 100, 'city' => 'Chattogram', 'address' => 'GEC Circle, Chattogram'],
            ['number' => 'SP-DEMO-0003', 'user' => $customers->get(2), 'coupon' => $save500, 'status' => Order::STATUS_SHIPPED, 'payment' => Order::PAYMENT_VERIFIED, 'shipping' => 120, 'city' => 'Sylhet', 'address' => 'Zindabazar, Sylhet'],
            ['number' => 'SP-DEMO-0004', 'user' => $customers->get(0), 'coupon' => null, 'status' => Order::STATUS_PENDING, 'payment' => Order::PAYMENT_UNPAID, 'shipping' => 70, 'city' => 'Dhaka', 'address' => 'Uttara, Dhaka'],
            ['number' => 'SP-DEMO-0005', 'user' => $customers->get(1), 'coupon' => null, 'status' => Order::STATUS_CANCELLED, 'payment' => Order::PAYMENT_REJECTED, 'shipping' => 70, 'city' => 'Dhaka', 'address' => 'Dhanmondi, Dhaka'],
        ];

        foreach ($rows as $row) {
            $user = $row['user'];
            $coupon = $row['coupon'];
            $order = Order::withTrashed()->updateOrCreate(
                ['order_number' => $row['number']],
                [
                    'user_id' => $user?->id,
                    'assigned_agent_id' => $agent?->id,
                    'coupon_id' => $coupon?->id,
                    'coupon_code' => $coupon?->code,
                    'buyer_name' => $user?->name ?? 'Guest Buyer',
                    'buyer_phone' => '01700000000',
                    'buyer_email' => $user?->email ?? 'guest@example.com',
                    'shipping_address' => $row['address'],
                    'city_or_area' => $row['city'],
                    'subtotal' => 0,
                    'discount' => 0,
                    'shipping' => $row['shipping'],
                    'grand_total' => $row['shipping'],
                    'payment_status' => $row['payment'],
                    'order_status' => $row['status'],
                    'customer_note' => 'Seeded demo order for frontend and admin testing.',
                    'internal_note' => 'Created by DatabaseSeeder.',
                ],
            );

            if ($order->trashed()) $order->restore();
        }
    }
}
