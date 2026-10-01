<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'SP-DEMO-0001' => [['SP-PH-128', 1], ['SP-EAR-01', 2]],
            'SP-DEMO-0002' => [['SP-SHOE-01', 2], ['SP-TEE-01', 2]],
            'SP-DEMO-0003' => [['SP-WATCH-01', 1], ['SP-BAG-01', 1], ['SP-BTL-01', 2]],
            'SP-DEMO-0004' => [['SP-AIR-45', 1]],
            'SP-DEMO-0005' => [['SP-DIN-16', 1]],
        ];

        foreach ($map as $orderNumber => $items) {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            foreach ($items as [$sku, $quantity]) {
                $product = Product::where('sku', $sku)->firstOrFail();
                $unitPrice = (float) ($product->sale_price ?: $product->regular_price);
                $lineTotal = round($unitPrice * $quantity, 2);

                $item = OrderItem::withTrashed()->updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $product->id],
                    [
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'unit_price' => $unitPrice,
                        'quantity' => $quantity,
                        'line_total' => $lineTotal,
                    ],
                );

                if ($item->trashed()) $item->restore();
            }

            $subtotal = (float) OrderItem::where('order_id', $order->id)->sum('line_total');
            $discount = $order->coupon ? $order->coupon->calculateDiscount($subtotal) : 0.0;
            $shipping = (float) $order->shipping;

            $order->update([
                'subtotal' => $subtotal,
                'discount' => $discount,
                'grand_total' => max(0, round($subtotal - $discount + $shipping, 2)),
            ]);
        }
    }
}
