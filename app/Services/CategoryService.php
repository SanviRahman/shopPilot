<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
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

            $category = Category::create($this->withoutMediaFields($data));
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

            $category->update($this->withoutMediaFields($data));
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
        $mediaFields = [
            'image' => 'category_image',
            'og_image' => 'category_og_image',
            'twitter_image' => 'category_twitter_image',
        ];

        foreach ($mediaFields as $field => $collection) {
            if (($data[$field] ?? null) instanceof UploadedFile) {
                // A new upload always wins over an existing picker selection.
                $category->addMedia($data[$field])->toMediaCollection($collection);
                continue;
            }

            $selectedMediaId = $data[$field . '_media_id'] ?? null;
            if ($selectedMediaId) {
                $source = Media::query()
                    ->whereKey($selectedMediaId)
                    ->where('mime_type', 'like', 'image/%')
                    ->firstOrFail();

                // Clone the source file so the original library item remains reusable.
                $category->addMedia($source->getPath())
                    ->usingName($source->name)
                    ->usingFileName($source->file_name)
                    ->toMediaCollection($collection);
                continue;
            }

            if (filter_var($data['remove_' . $field] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $category->clearMediaCollection($collection);
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