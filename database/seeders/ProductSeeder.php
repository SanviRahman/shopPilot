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
        $catalog = $this->catalog();

        Category::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->each(function (Category $category) use ($catalog): void {
                $products = $catalog[$category->slug] ?? $this->fallbackProducts($category);

                foreach (array_slice($products, 0, 10) as $index => $data) {
                    $name = $data['name'];
                    $sku = $data['sku'] ?? $this->fallbackSku($category, $index);
                    $short = $data['short'] ?? sprintf(
                        'Quality %s selected for the %s collection at ShopPilot.',
                        strtolower($name),
                        $category->name,
                    );

                    $product = Product::withTrashed()->updateOrCreate(
                        ['sku' => $sku],
                        [
                            'category_id' => $category->id,
                            'name' => $name,
                            'slug' => Str::slug($name),
                            'short_description' => $short,
                            'description' => $data['description'] ?? $short . ' Carefully selected for value, reliability and everyday use.',
                            'regular_price' => $data['regular'],
                            'sale_price' => $data['sale'] ?? null,
                            'stock_quantity' => $data['stock'] ?? 20,
                            'status' => 'active',
                            'featured' => $data['featured'] ?? $index < 2,
                            'meta_title' => $name . ' | ShopPilot',
                            'meta_description' => $short,
                            'meta_keywords' => implode(', ', array_filter([
                                Str::lower($category->name),
                                Str::lower($name),
                                'shoppilot',
                            ])),
                        ],
                    );

                    if ($product->trashed()) {
                        $product->restore();
                    }
                }
            });
    }

    private function catalog(): array
    {
        return [
            'electronics' => [
                ['name' => 'Smartphone 128GB', 'sku' => 'SP-PH-128', 'regular' => 22000, 'sale' => 18500, 'stock' => 24, 'featured' => true],
                ['name' => 'Smart Watch Pro', 'sku' => 'SP-WATCH-01', 'regular' => 5000, 'sale' => 4500, 'stock' => 18, 'featured' => true],
                ['name' => 'Wireless Earbuds', 'sku' => 'SP-EAR-01', 'regular' => 3200, 'sale' => 2800, 'stock' => 27],
                ['name' => 'Mechanical Keyboard', 'sku' => 'SP-KEY-01', 'regular' => 4600, 'sale' => 4200, 'stock' => 26],
                ['name' => 'Portable Bluetooth Speaker', 'sku' => 'SP-BTS-01', 'regular' => 3800, 'sale' => 3350, 'stock' => 31],
                ['name' => '20000mAh Power Bank', 'sku' => 'SP-PWB-20K', 'regular' => 3500, 'sale' => 3100, 'stock' => 33],
                ['name' => '24-inch Full HD Monitor', 'sku' => 'SP-MON-24', 'regular' => 17500, 'sale' => 15900, 'stock' => 15],
                ['name' => 'Wireless Optical Mouse', 'sku' => 'SP-MOUSE-01', 'regular' => 1500, 'sale' => 1250, 'stock' => 46],
                ['name' => '65W USB-C Fast Charger', 'sku' => 'SP-CHG-65', 'regular' => 2500, 'sale' => 2200, 'stock' => 38],
                ['name' => 'Full HD USB Webcam', 'sku' => 'SP-WEB-01', 'regular' => 4200, 'sale' => 3750, 'stock' => 22],
            ],
            'fashion' => [
                ['name' => "Men's Running Shoes", 'sku' => 'SP-SHOE-01', 'regular' => 4000, 'sale' => 3200, 'stock' => 32, 'featured' => true],
                ['name' => 'Premium Cotton T-Shirt', 'sku' => 'SP-TEE-01', 'regular' => 1200, 'sale' => 950, 'stock' => 60, 'featured' => true],
                ['name' => "Men's Casual Shirt", 'sku' => 'SP-SHIRT-01', 'regular' => 1850, 'sale' => 1550, 'stock' => 42],
                ['name' => "Women's Summer Dress", 'sku' => 'SP-DRESS-01', 'regular' => 2900, 'sale' => 2450, 'stock' => 28],
                ['name' => 'Classic Denim Jacket', 'sku' => 'SP-DENIM-01', 'regular' => 3400, 'sale' => 2990, 'stock' => 21],
                ['name' => 'Everyday Pullover Hoodie', 'sku' => 'SP-HOOD-01', 'regular' => 2600, 'sale' => 2250, 'stock' => 30],
                ['name' => "Women's Casual Sneakers", 'sku' => 'SP-WSHOE-01', 'regular' => 3600, 'sale' => 3150, 'stock' => 29],
                ['name' => 'Classic Polo Shirt', 'sku' => 'SP-POLO-01', 'regular' => 1700, 'sale' => 1450, 'stock' => 48],
                ['name' => "Women's Printed Kurti", 'sku' => 'SP-KURTI-01', 'regular' => 2200, 'sale' => 1890, 'stock' => 35],
                ['name' => 'Genuine Leather Belt', 'sku' => 'SP-BELT-01', 'regular' => 1400, 'sale' => 1150, 'stock' => 50],
            ],
            'home-living' => [
                ['name' => 'Air Fryer 4.5L', 'sku' => 'SP-AIR-45', 'regular' => 9500, 'sale' => 8900, 'stock' => 14, 'featured' => true],
                ['name' => 'Adjustable LED Desk Lamp', 'sku' => 'SP-LAMP-01', 'regular' => 3300, 'sale' => 2900, 'stock' => 22, 'featured' => true],
                ['name' => 'Ceramic Dinner Set', 'sku' => 'SP-DIN-16', 'regular' => 3900, 'sale' => 3500, 'stock' => 20],
                ['name' => 'Electric Kettle 1.8L', 'sku' => 'SP-KET-18', 'regular' => 2800, 'sale' => 2490, 'stock' => 26],
                ['name' => 'Nonstick Cookware Set', 'sku' => 'SP-COOK-01', 'regular' => 6800, 'sale' => 6100, 'stock' => 17],
                ['name' => 'Premium Bedsheet Set', 'sku' => 'SP-BED-01', 'regular' => 2400, 'sale' => 1990, 'stock' => 34],
                ['name' => 'Modern Wall Clock', 'sku' => 'SP-CLOCK-01', 'regular' => 1800, 'sale' => 1550, 'stock' => 41],
                ['name' => 'Foldable Storage Organizer', 'sku' => 'SP-ORG-01', 'regular' => 2100, 'sale' => 1850, 'stock' => 36],
                ['name' => 'Rechargeable Table Fan', 'sku' => 'SP-FAN-01', 'regular' => 4200, 'sale' => 3750, 'stock' => 19],
                ['name' => 'Decorative Cushion Set', 'sku' => 'SP-CUSH-01', 'regular' => 1900, 'sale' => 1650, 'stock' => 44],
            ],
            'beauty-care' => [
                ['name' => 'Vitamin C Face Serum', 'sku' => 'SP-SERUM-01', 'regular' => 1800, 'sale' => 1490, 'stock' => 36, 'featured' => true],
                ['name' => 'Daily Moisturizing Cream', 'sku' => 'SP-CREAM-01', 'regular' => 1600, 'sale' => 1350, 'stock' => 42, 'featured' => true],
                ['name' => 'Sunscreen SPF 50', 'sku' => 'SP-SUNSC-50', 'regular' => 1500, 'sale' => 1290, 'stock' => 48],
                ['name' => 'Gentle Face Wash', 'sku' => 'SP-FWASH-01', 'regular' => 950, 'sale' => 820, 'stock' => 55],
                ['name' => 'Professional Hair Dryer', 'sku' => 'SP-HDRY-01', 'regular' => 3200, 'sale' => 2850, 'stock' => 24],
                ['name' => 'Ceramic Hair Straightener', 'sku' => 'SP-HSTR-01', 'regular' => 3500, 'sale' => 3100, 'stock' => 22],
                ['name' => 'Hydrating Body Lotion', 'sku' => 'SP-LOTION-01', 'regular' => 1200, 'sale' => 990, 'stock' => 50],
                ['name' => 'Matte Lipstick Set', 'sku' => 'SP-LIP-SET', 'regular' => 2200, 'sale' => 1850, 'stock' => 31],
                ['name' => 'Signature Eau de Parfum', 'sku' => 'SP-PERF-01', 'regular' => 4200, 'sale' => 3650, 'stock' => 18],
                ['name' => 'Makeup Brush Set', 'sku' => 'SP-BRUSH-01', 'regular' => 2000, 'sale' => 1690, 'stock' => 39],
            ],
            'sports-outdoors' => [
                ['name' => 'Stainless Water Bottle', 'sku' => 'SP-BTL-01', 'regular' => 1450, 'sale' => 1200, 'stock' => 55, 'featured' => true],
                ['name' => 'Premium Yoga Mat', 'sku' => 'SP-YOGA-01', 'regular' => 2200, 'sale' => 1900, 'stock' => 40, 'featured' => true],
                ['name' => 'Adjustable Dumbbell Pair', 'sku' => 'SP-DUMB-01', 'regular' => 6500, 'sale' => 5900, 'stock' => 16],
                ['name' => 'Resistance Band Set', 'sku' => 'SP-BAND-01', 'regular' => 1800, 'sale' => 1490, 'stock' => 47],
                ['name' => 'Training Football', 'sku' => 'SP-BALL-01', 'regular' => 2100, 'sale' => 1850, 'stock' => 38],
                ['name' => 'Badminton Racket Set', 'sku' => 'SP-BADM-01', 'regular' => 3800, 'sale' => 3350, 'stock' => 25],
                ['name' => 'Speed Skipping Rope', 'sku' => 'SP-ROPE-01', 'regular' => 900, 'sale' => 750, 'stock' => 64],
                ['name' => 'Training Gym Gloves', 'sku' => 'SP-GLOVE-01', 'regular' => 1250, 'sale' => 1050, 'stock' => 49],
                ['name' => 'Rechargeable Camping Lantern', 'sku' => 'SP-LANT-01', 'regular' => 2600, 'sale' => 2250, 'stock' => 28],
                ['name' => 'Outdoor Sports Backpack', 'sku' => 'SP-SBAG-01', 'regular' => 2900, 'sale' => 2500, 'stock' => 34],
            ],
            'baby-kids' => [
                ['name' => 'Soft Teddy Bear', 'sku' => 'SP-TEDDY-01', 'regular' => 1800, 'sale' => 1490, 'stock' => 34, 'featured' => true],
                ['name' => 'Baby Feeding Bottle Set', 'sku' => 'SP-BOTTLE-01', 'regular' => 1400, 'sale' => 1190, 'stock' => 48, 'featured' => true],
                ['name' => 'Kids Building Blocks', 'sku' => 'SP-BLOCK-01', 'regular' => 2500, 'sale' => 2150, 'stock' => 30],
                ['name' => 'Baby Diaper Pack', 'sku' => 'SP-DIAPER-01', 'regular' => 1900, 'sale' => 1650, 'stock' => 52],
                ['name' => 'Soft Baby Blanket', 'sku' => 'SP-BLANKET-01', 'regular' => 2200, 'sale' => 1850, 'stock' => 27],
                ['name' => 'Kids Drawing Set', 'sku' => 'SP-DRAW-01', 'regular' => 1200, 'sale' => 990, 'stock' => 43],
                ['name' => 'Baby Bath Towel Set', 'sku' => 'SP-BTOWEL-01', 'regular' => 1500, 'sale' => 1250, 'stock' => 35],
                ['name' => 'Kids School Backpack', 'sku' => 'SP-KBAG-01', 'regular' => 2600, 'sale' => 2190, 'stock' => 29],
                ['name' => 'Baby Musical Toy', 'sku' => 'SP-MTOY-01', 'regular' => 1700, 'sale' => 1450, 'stock' => 37],
                ['name' => 'Kids Insulated Water Bottle', 'sku' => 'SP-KBTL-01', 'regular' => 1300, 'sale' => 1100, 'stock' => 46],
            ],
            'groceries' => [
                ['name' => 'Premium Basmati Rice 5kg', 'sku' => 'SP-RICE-05', 'regular' => 1200, 'sale' => 1100, 'stock' => 80, 'featured' => true],
                ['name' => 'Soybean Cooking Oil 5L', 'sku' => 'SP-OIL-05', 'regular' => 1050, 'sale' => 990, 'stock' => 75, 'featured' => true],
                ['name' => 'Red Lentils 1kg', 'sku' => 'SP-DAL-01', 'regular' => 190, 'sale' => 175, 'stock' => 120],
                ['name' => 'Premium Atta 2kg', 'sku' => 'SP-ATTA-02', 'regular' => 180, 'sale' => 165, 'stock' => 110],
                ['name' => 'Refined Sugar 1kg', 'sku' => 'SP-SUGAR-01', 'regular' => 160, 'sale' => 150, 'stock' => 125],
                ['name' => 'Milk Powder 500g', 'sku' => 'SP-MILK-500', 'regular' => 850, 'sale' => 790, 'stock' => 68],
                ['name' => 'Premium Black Tea 400g', 'sku' => 'SP-TEA-400', 'regular' => 480, 'sale' => 440, 'stock' => 72],
                ['name' => 'Mixed Spices Family Pack', 'sku' => 'SP-SPICE-01', 'regular' => 650, 'sale' => 590, 'stock' => 66],
                ['name' => 'Pure Honey 500g', 'sku' => 'SP-HONEY-500', 'regular' => 780, 'sale' => 720, 'stock' => 54],
                ['name' => 'Instant Noodles 8 Pack', 'sku' => 'SP-NOODLE-08', 'regular' => 520, 'sale' => 480, 'stock' => 90],
            ],
            'accessories' => [
                ['name' => 'Laptop Backpack', 'sku' => 'SP-BAG-01', 'regular' => 2500, 'sale' => 2200, 'stock' => 40, 'featured' => true],
                ['name' => 'UV Protection Sunglasses', 'sku' => 'SP-SUN-01', 'regular' => 2100, 'sale' => 1850, 'stock' => 35, 'featured' => true],
                ['name' => 'Premium Leather Wallet', 'sku' => 'SP-WALLET-01', 'regular' => 1800, 'sale' => 1500, 'stock' => 44],
                ['name' => 'Classic Analog Wrist Watch', 'sku' => 'SP-AWATCH-01', 'regular' => 3200, 'sale' => 2750, 'stock' => 28],
                ['name' => 'Shockproof Phone Case', 'sku' => 'SP-CASE-01', 'regular' => 850, 'sale' => 690, 'stock' => 70],
                ['name' => 'Braided Type-C Cable', 'sku' => 'SP-CABLE-01', 'regular' => 700, 'sale' => 550, 'stock' => 85],
                ['name' => 'Travel Organizer Pouch', 'sku' => 'SP-TRAVEL-01', 'regular' => 1400, 'sale' => 1190, 'stock' => 51],
                ['name' => 'Classic Baseball Cap', 'sku' => 'SP-CAP-01', 'regular' => 1100, 'sale' => 890, 'stock' => 58],
                ['name' => 'Minimal Keychain Set', 'sku' => 'SP-KEYCHAIN-01', 'regular' => 600, 'sale' => 490, 'stock' => 95],
                ['name' => 'Automatic Folding Umbrella', 'sku' => 'SP-UMB-01', 'regular' => 1600, 'sale' => 1350, 'stock' => 47],
            ],
        ];
    }

    private function fallbackProducts(Category $category): array
    {
        return collect(range(1, 10))
            ->map(fn (int $index): array => [
                'name' => sprintf('%s Product %02d', $category->name, $index),
                'sku' => $this->fallbackSku($category, $index - 1),
                'regular' => 1000 + ($index * 350),
                'sale' => 900 + ($index * 300),
                'stock' => 15 + ($index * 3),
                'featured' => $index <= 2,
            ])
            ->all();
    }

    private function fallbackSku(Category $category, int $index): string
    {
        $prefix = Str::upper(Str::substr(preg_replace('/[^a-z0-9]/i', '', $category->slug), 0, 8));

        return sprintf('SP-%s-%02d', $prefix ?: 'CAT', $index + 1);
    }
}
