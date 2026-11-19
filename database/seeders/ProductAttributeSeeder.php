<?php

namespace Database\Seeders;

use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Color' => ['display_type' => 'color', 'values' => [
                'Red' => '#ef4444',
                'Black' => '#171717',
                'Blue' => '#2563eb',
                'White' => '#ffffff',
                'Green' => '#16a34a',
            ]],
            'Size' => ['display_type' => 'button', 'values' => [
                'XS' => null,
                'S' => null,
                'M' => null,
                'L' => null,
                'XL' => null,
                'XXL' => null,
            ]],
        ];

        foreach ($attributes as $name => $definition) {
            $attribute = ProductAttribute::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'display_type' => $definition['display_type'], 'is_active' => true],
            );

            foreach ($definition['values'] as $label => $colorCode) {
                $attribute->values()->firstOrCreate(
                    ['slug' => Str::slug($label)],
                    [
                        'label' => $label,
                        'color_code' => $colorCode,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
