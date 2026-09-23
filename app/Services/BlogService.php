<?php

namespace App\Services;

use App\Models\Blog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
                    $sub->where('title', 'like', $term)
                        ->orWhere('slug', 'like', $term);
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Store a new blog post.
     */
    public function create(array $data, $author = null): Blog
    {
        $slug = ! empty($data['slug'])
            ? Str::slug($data['slug'])
            : Str::slug($data['title']) . '-' . uniqid();

        return Blog::create([
            'author_type' => $author ? get_class($author) : null,
            'author_id'   => $author?->id,
            'title'       => $data['title'],
            'slug'        => $slug,
            'content'     => $data['content'] ?? null,
        ]);
    }

    /**
     * Update an existing blog post.
     */
    public function update(Blog $blog, array $data): bool
    {
        $slug = ! empty($data['slug'])
            ? Str::slug($data['slug'])
            : Str::slug($data['title']) . '-' . $blog->id;

        return $blog->update([
            'title'   => $data['title'],
            'slug'    => $slug,
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
                'delete'                  => Blog::query()->find($id),
                'restore', 'force-delete' => Blog::onlyTrashed()->find($id),
                default                   => null,
            };

            if (! $blog) {
                continue;
            }

            match ($action) {
                'delete'       => $blog->delete(),
                'restore'      => $blog->restore(),
                'force-delete' => $blog->forceDelete(),
            };

            $processed++;
        }

        return $processed;
    }
}