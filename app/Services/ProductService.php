<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductService
{
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $data['slug'] = blank($data['slug'] ?? null)
                ? Str::slug($data['name'])
                : Str::slug($data['slug']);

            $product = Product::create($this->withoutMediaFields($data));
            $this->handleMediaUploads($product, $data);

            return $product;
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $data['slug'] = blank($data['slug'] ?? null)
                ? Str::slug($data['name'])
                : Str::slug($data['slug']);

            $product->update($this->withoutMediaFields($data));
            $this->handleMediaUploads($product, $data);

            return $product->refresh();
        });
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function restore(Product $product): void
    {
        $product->restore();
    }

    public function forceDelete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->clearMediaCollection('product_thumbnail');
            $product->clearMediaCollection('product_gallery');
            $product->clearMediaCollection('product_og_image');
            $product->clearMediaCollection('product_twitter_image');
            $product->forceDelete();
        });
    }

    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $product = Product::withTrashed()->find($id);

                if (! $product) {
                    $skipped++;
                    continue;
                }

                match ($action) {
                    'delete' => $this->delete($product),
                    'restore' => $product->trashed() ? $this->restore($product) : null,
                    'force-delete' => $product->trashed() ? $this->forceDelete($product) : null,
                    default => null,
                };
                $processed++;
            }

            return compact('processed', 'skipped');
        });
    }

    private function handleMediaUploads(Product $product, array $data): void
    {
        $mediaFields = [
            'image' => 'product_thumbnail',
            'og_image' => 'product_og_image',
            'twitter_image' => 'product_twitter_image',
        ];

        foreach ($mediaFields as $field => $collection) {
            if (($data[$field] ?? null) instanceof UploadedFile) {
                $product->addMedia($data[$field])->toMediaCollection($collection);
                continue;
            }

            $selectedMediaId = $data[$field . '_media_id'] ?? null;
            if ($selectedMediaId) {
                $source = Media::query()
                    ->whereKey($selectedMediaId)
                    ->where('mime_type', 'like', 'image/%')
                    ->firstOrFail();

                $product->addMedia($source->getPath())
                    ->usingName($source->name)
                    ->usingFileName($source->file_name)
                    ->toMediaCollection($collection);
                continue;
            }

            if (filter_var($data['remove_' . $field] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $product->clearMediaCollection($collection);
            }
        }
    }

    private function withoutMediaFields(array $data): array
    {
        return Arr::except($data, [
            'image',
            'image_media_id',
            'og_image',
            'og_image_media_id',
            'twitter_image',
            'twitter_image_media_id',
            'remove_image',
            'remove_og_image',
            'remove_twitter_image',
        ]);
    }
}