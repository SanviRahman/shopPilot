<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Throwable;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $source = database_path('seeders/assets/shoppilot-seed.png');

        if (! is_file($source)) {
            $this->command?->warn('MediaSeeder skipped: seed media asset is missing.');
            return;
        }

        $targets = [
            [Admin::first(), 'avatar'],
            [User::first(), 'avatar'],
            [Category::first(), 'category_image'],
            [Product::first(), 'product_thumbnail'],
        ];

        foreach ($targets as [$model, $collection]) {
            if (! $model || $model->hasMedia($collection)) {
                continue;
            }

            try {
                $model->addMedia($source)
                    ->preservingOriginal()
                    ->usingFileName($model->getTable() . '-' . $model->getKey() . '.png')
                    ->toMediaCollection($collection);
            } catch (Throwable $exception) {
                $this->command?->warn('MediaSeeder skipped one attachment: ' . $exception->getMessage());
            }
        }
    }
}
