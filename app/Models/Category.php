<?php

namespace App\Models;

use App\Traits\HasSEO;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Category extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, HasSEO;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'status',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'twitter_title',
        'twitter_description',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('category_image')->singleFile();
        $this->addMediaCollection('category_og_image')->singleFile();
        $this->addMediaCollection('category_twitter_image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(100)->height(100)->sharpen(10);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('category_image') ?: null;
    }

    /**
     * Recursively retrieve all descendant category IDs (children, grandchildren, etc.).
     * Used to prevent circular tree loops.
     *
     * @return array<int, int>
     */
    public function allDescendantIds(): array
    {
        $ids = [];
        $children = $this->relationLoaded('children') ? $this->children : $this->children()->get();

        foreach ($children as $child) {
            $ids[] = (int) $child->id;
            $ids = array_merge($ids, $child->allDescendantIds());
        }

        return array_values(array_unique($ids));
    }

    /**
     * Generate hierarchical category tree options for dropdown selects.
     *
     * @param int|null $excludeCategoryId Category ID being edited (to disable itself and all its descendants)
     * @param bool $onlyActive Whether to include only active categories
     * @return array<int, array{id: int, name: string, depth: int, indent: string, disabled: bool, parent_id: int|null}>
     */
    public static function getTreeSelectOptions(?int $excludeCategoryId = null, bool $onlyActive = false): array
    {
        $query = static::query()->orderBy('sort_order')->orderBy('name');
        if ($onlyActive) {
            $query->where('status', 'active');
        }
        $allCategories = $query->get();

        $disabledIds = [];
        if ($excludeCategoryId !== null) {
            $disabledIds[] = $excludeCategoryId;
            $target = $allCategories->firstWhere('id', $excludeCategoryId);
            if ($target) {
                $disabledIds = array_merge($disabledIds, $target->allDescendantIds());
            }
        }
        $disabledIds = array_map('intval', array_unique($disabledIds));

        $grouped = $allCategories->groupBy(fn ($cat) => (int) ($cat->parent_id ?? 0));
        $options = [];

        $traverse = function (int $parentId, int $depth) use (&$traverse, &$options, $grouped, $disabledIds) {
            if (! isset($grouped[$parentId])) {
                return;
            }

            foreach ($grouped[$parentId] as $cat) {
                $isDisabled = in_array((int) $cat->id, $disabledIds, true);
                $indent = str_repeat('— ', $depth);

                $options[] = [
                    'id' => (int) $cat->id,
                    'name' => $cat->name,
                    'depth' => $depth,
                    'indent' => $indent,
                    'disabled' => $isDisabled,
                    'parent_id' => $cat->parent_id ? (int) $cat->parent_id : null,
                ];

                $traverse((int) $cat->id, $depth + 1);
            }
        };

        $traverse(0, 0);

        return $options;
    }
}