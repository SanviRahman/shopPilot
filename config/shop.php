<?php

return [
    'checkout' => [
        'shipping_methods' => [
            'standard' => [
                'label' => 'Standard Delivery',
                'description' => 'Delivery within 3-5 business days',
                'fee' => (float) env('SHOP_STANDARD_SHIPPING_FEE', 0),
                'icon' => 'fa-truck',
            ],
            'express' => [
                'label' => 'Express Delivery',
                'description' => 'Delivery within 1-2 business days',
                'fee' => (float) env('SHOP_EXPRESS_SHIPPING_FEE', 120),
                'icon' => 'fa-bolt',
            ],
        ],
        'manual_payment_codes' => ['bkash', 'nagad', 'rocket'],
    ],
];
