<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'WELCOME10', 'discount_type' => 'percentage', 'discount_value' => 10, 'minimum_order_amount' => 1000, 'start_date' => now()->subDays(7), 'end_date' => now()->addMonths(6), 'status' => 'active'],
            ['code' => 'SAVE500', 'discount_type' => 'fixed', 'discount_value' => 500, 'minimum_order_amount' => 5000, 'start_date' => now()->subDay(), 'end_date' => now()->addMonths(3), 'status' => 'active'],
            ['code' => 'VIP15', 'discount_type' => 'percentage', 'discount_value' => 15, 'minimum_order_amount' => 10000, 'start_date' => now(), 'end_date' => now()->addMonth(), 'status' => 'active'],
        ];

        foreach ($coupons as $data) {
            $coupon = Coupon::withTrashed()->updateOrCreate(['code' => $data['code']], $data);
            if ($coupon->trashed()) $coupon->restore();
        }
    }
}
