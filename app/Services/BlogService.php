<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogService
{
    /**
     * Get paginated blogs with optional filters.
     */
    public function getPaginatedBlogs(array $filters = [], bool $isTrash = false, int $perPage = 15): LengthAwarePaginator
    {
        $query = $isTrash ? Blog::onlyTrashed() : Blog::query();

        return $query
            ->with('author')
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $term = '%' . trim($filters['search']) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', $term)->orWhere('slug', 'like', $term)->orWhere('description', 'like', $term)->orWhere('content', 'like', $term);
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Store a new blog post and keep direct FK ownership in sync with the morph author.
     */
    public function create(array $data, ?Model $author = null): Blog
    {
        $slug = $this->uniqueSlug(
            ! empty($data['slug']) ? (string) $data['slug'] : (string) $data['title'],
        );

        return Blog::create([
            'admin_id' => $author instanceof Admin ? $author->id : null,
            'user_id' => $author instanceof User ? $author->id : null,
            'author_type' => $author ? $author::class : null,
            'author_id' => $author?->getKey(),
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'content' => $data['content'] ?? null,
        ]);
    }

    /**
     * Update an existing blog post.
     */
    public function update(Blog $blog, array $data): bool
    {
        $baseSlug = ! empty($data['slug']) ? (string) $data['slug'] : (string) $data['title'];
        $slug = $this->uniqueSlug($baseSlug, $blog->id);

        return $blog->update([
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'content' => $data['content'] ?? null,
        ]);
    }

    /**
     * Move a blog post to trash.
     */
    public function moveToTrash(Blog $blog): bool
    {
        return (bool) $blog->delete();
    }

    /**
     * Restore a trashed blog post.
     */
    public function restore(int $id): bool
    {
        $blog = Blog::onlyTrashed()->findOrFail($id);

        return (bool) $blog->restore();
    }

    /**
     * Permanently delete a blog post.
     */
    public function forceDelete(int $id): bool
    {
        $blog = Blog::onlyTrashed()->findOrFail($id);

        return (bool) $blog->forceDelete();
    }

    /**
     * Perform bulk actions on blogs.
     */
    public function bulkAction(string $action, array $ids): int
    {
        $processed = 0;
        $uniqueIds = array_unique(array_map('intval', $ids));

        foreach ($uniqueIds as $id) {
            $blog = match ($action) {
                'delete' => Blog::query()->find($id),
                'restore', 'force-delete' => Blog::onlyTrashed()->find($id),
                default => null,
            };

            if (! $blog) {
                continue;
            }

            match ($action) {
                'delete' => $blog->delete(),
                'restore' => $blog->restore(),
                'force-delete' => $blog->forceDelete(),
            };

            $processed++;
        }

        return $processed;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? $base : 'blog';
        $slug = $base;
        $suffix = 2;

        while (Blog::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
