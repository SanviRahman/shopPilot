<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Smart devices, gadgets and everyday electronics.'],
            ['name' => 'Fashion', 'description' => 'Modern clothing, footwear and fashion essentials.'],
            ['name' => 'Home & Living', 'description' => 'Useful products for a comfortable modern home.'],
            ['name' => 'Beauty & Care', 'description' => 'Beauty, skincare and personal care essentials.'],
            ['name' => 'Sports & Outdoors', 'description' => 'Fitness, sports and outdoor lifestyle products.'],
            ['name' => 'Baby & Kids', 'description' => 'Practical and fun essentials for children.'],
            ['name' => 'Groceries', 'description' => 'Daily grocery and pantry essentials.'],
            ['name' => 'Accessories', 'description' => 'Useful lifestyle and fashion accessories.'],
        ];

        foreach ($categories as $index => $data) {
            $category = Category::withTrashed()->updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'status' => 'active',
                    'sort_order' => $index + 1,
                    'meta_title' => $data['name'] . ' | ShopPilot',
                    'meta_description' => $data['description'],
                ],
            );

            if ($category->trashed()) {
                $category->restore();
            }
        }
    }
}
