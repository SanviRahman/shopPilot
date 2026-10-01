<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['category' => 'electronics', 'name' => 'Smartphone 128GB', 'sku' => 'SP-PH-128', 'regular' => 22000, 'sale' => 18500, 'stock' => 24, 'featured' => true],
            ['category' => 'fashion', 'name' => "Men's Running Shoes", 'sku' => 'SP-SHOE-01', 'regular' => 4000, 'sale' => 3200, 'stock' => 32, 'featured' => true],
            ['category' => 'electronics', 'name' => 'Smart Watch Pro', 'sku' => 'SP-WATCH-01', 'regular' => 5000, 'sale' => 4500, 'stock' => 18, 'featured' => true],
            ['category' => 'accessories', 'name' => 'Laptop Backpack', 'sku' => 'SP-BAG-01', 'regular' => 2500, 'sale' => 2200, 'stock' => 40, 'featured' => true],
            ['category' => 'electronics', 'name' => 'Wireless Earbuds', 'sku' => 'SP-EAR-01', 'regular' => 3200, 'sale' => 2800, 'stock' => 27, 'featured' => true],
            ['category' => 'home-living', 'name' => 'Air Fryer 4.5L', 'sku' => 'SP-AIR-45', 'regular' => 9500, 'sale' => 8900, 'stock' => 14, 'featured' => true],
            ['category' => 'electronics', 'name' => 'Mechanical Keyboard', 'sku' => 'SP-KEY-01', 'regular' => 4600, 'sale' => 4200, 'stock' => 26, 'featured' => false],
            ['category' => 'fashion', 'name' => 'Premium Cotton T-Shirt', 'sku' => 'SP-TEE-01', 'regular' => 1200, 'sale' => 950, 'stock' => 60, 'featured' => false],
            ['category' => 'home-living', 'name' => 'Ceramic Dinner Set', 'sku' => 'SP-DIN-16', 'regular' => 3900, 'sale' => 3500, 'stock' => 20, 'featured' => false],
            ['category' => 'sports-outdoors', 'name' => 'Stainless Water Bottle', 'sku' => 'SP-BTL-01', 'regular' => 1450, 'sale' => 1200, 'stock' => 55, 'featured' => false],
            ['category' => 'accessories', 'name' => 'UV Protection Sunglasses', 'sku' => 'SP-SUN-01', 'regular' => 2100, 'sale' => 1850, 'stock' => 35, 'featured' => false],
            ['category' => 'home-living', 'name' => 'Adjustable LED Desk Lamp', 'sku' => 'SP-LAMP-01', 'regular' => 3300, 'sale' => 2900, 'stock' => 22, 'featured' => false],
        ];

        foreach ($products as $data) {
            $category = Category::where('slug', $data['category'])->firstOrFail();
            $slug = Str::slug($data['name']);
            $short = 'Quality ' . strtolower($data['name']) . ' selected for a reliable ShopPilot shopping experience.';

            $product = Product::withTrashed()->updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'slug' => $slug,
                    'short_description' => $short,
                    'description' => $short . ' Carefully selected for value, quality and everyday use.',
                    'regular_price' => $data['regular'],
                    'sale_price' => $data['sale'],
                    'stock_quantity' => $data['stock'],
                    'status' => 'active',
                    'featured' => $data['featured'],
                    'meta_title' => $data['name'] . ' | ShopPilot',
                    'meta_description' => $short,
                ],
            );

            if ($product->trashed()) {
                $product->restore();
            }
        }
    }
}
