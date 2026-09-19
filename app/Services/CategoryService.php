<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryService
{
    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data): Category {
            $data['slug'] = blank($data['slug'] ?? null)
                ? Str::slug($data['name'])
                : Str::slug($data['slug']);

            if (! isset($data['sort_order'])) {
                $data['sort_order'] = (Category::max('sort_order') ?? 0) + 1;
            }

            $category = Category::create($data);
            $this->handleMediaUploads($category, $data);

            return $category;
        });
    }

    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data): Category {
            $data['slug'] = blank($data['slug'] ?? null)
                ? Str::slug($data['name'])
                : Str::slug($data['slug']);

            $category->update($data);
            $this->handleMediaUploads($category, $data);

            return $category->refresh();
        });
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function restore(Category $category): void
    {
        $category->restore();
    }

    public function forceDelete(Category $category): void
    {
        $category->clearMediaCollection('category_image');
        $category->clearMediaCollection('category_og_image');
        $category->clearMediaCollection('category_twitter_image');
        $category->forceDelete();
    }

    public function reorder(array $orderData): void
    {
        DB::transaction(function () use ($orderData) {
            foreach ($orderData as $item) {
                if (isset($item['id'], $item['sort_order'])) {
                    Category::where('id', $item['id'])->update(['sort_order' => (int) $item['sort_order']]);
                }
            }
        });
    }

    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $category = Category::withTrashed()->find($id);

                if (! $category) {
                    $skipped++;
                    continue;
                }

                match ($action) {
                    'delete' => $this->delete($category),
                    'restore' => $category->trashed() ? $this->restore($category) : null,
                    'force-delete' => $category->trashed() ? $this->forceDelete($category) : null,
                    default => null,
                };
                $processed++;
            }

            return compact('processed', 'skipped');
        });
    }

    private function handleMediaUploads(Category $category, array $data): void
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $category->addMedia($data['image'])->toMediaCollection('category_image');
        }

        if (isset($data['og_image']) && $data['og_image'] instanceof UploadedFile) {
            $category->addMedia($data['og_image'])->toMediaCollection('category_og_image');
        }

        if (isset($data['twitter_image']) && $data['twitter_image'] instanceof UploadedFile) {
            $category->addMedia($data['twitter_image'])->toMediaCollection('category_twitter_image');
        }
    }
}