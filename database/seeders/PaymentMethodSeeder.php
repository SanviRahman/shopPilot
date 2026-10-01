<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'bKash', 'code' => 'bkash', 'account_number' => '01XXXXXXXXX', 'account_type' => 'merchant', 'instruction' => 'Send payment to the bKash merchant number and submit the transaction ID.', 'status' => 'active'],
            ['name' => 'Nagad', 'code' => 'nagad', 'account_number' => '01XXXXXXXXX', 'account_type' => 'merchant', 'instruction' => 'Send payment to the Nagad merchant number and submit the transaction ID.', 'status' => 'active'],
            ['name' => 'Cash on Delivery', 'code' => 'cod', 'account_number' => 'N/A', 'account_type' => 'cash_on_delivery', 'instruction' => 'Pay the delivery representative when your order arrives.', 'status' => 'active'],
        ];

        foreach ($methods as $data) {
            $method = PaymentMethod::withTrashed()->updateOrCreate(['code' => $data['code']], $data);
            if ($method->trashed()) $method->restore();
        }
    }
}
