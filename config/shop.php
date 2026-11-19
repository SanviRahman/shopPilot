<?php

return [
    'marketplace' => [
        'currency_code' => env('SHOP_MARKETPLACE_CURRENCY', 'BDT'),
        'currency_symbol' => env('SHOP_MARKETPLACE_CURRENCY_SYMBOL', '৳'),
        'minimum_payout_amount' => (float) env('SHOP_MINIMUM_PAYOUT_AMOUNT', 0),
        'maximum_payout_amount' => (float) env('SHOP_MAXIMUM_PAYOUT_AMOUNT', 9999999999),
        'maximum_vendor_shipping_fee' => (float) env('SHOP_MAXIMUM_VENDOR_SHIPPING_FEE', 100000),
    ],
    'vendor_products' => [
        'require_moderation' => (bool) env('SHOP_VENDOR_PRODUCTS_REQUIRE_MODERATION', true),
    ],
    'inventory' => [
        'low_stock_threshold' => (int) env('SHOP_LOW_STOCK_THRESHOLD', 5),
    ],
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
