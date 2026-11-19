<?php

namespace App\Services;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductAttributeService
{
    public function create(array $data): ProductAttribute
    {
        return DB::transaction(function () use ($data): ProductAttribute {
            $values = $data['values'];
            $attribute = ProductAttribute::create([
                ...Arr::except($data, ['values']),
                'slug' => $this->uniqueSlug($data['slug'] ?: $data['name']),
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->syncValues($attribute, $values);

            return $attribute->load('values');
        });
    }

    public function update(ProductAttribute $attribute, array $data): ProductAttribute
    {
        return DB::transaction(function () use ($attribute, $data): ProductAttribute {
            $values = $data['values'];

            if (! ($data['is_active'] ?? false) && $attribute->values()->whereHas('variants', fn ($query) => $query->withTrashed())->exists()) {
                throw ValidationException::withMessages([
                    'is_active' => 'This attribute is used by existing product variations and cannot be deactivated.',
                ]);
            }

            $attribute->update([
                ...Arr::except($data, ['values']),
                'slug' => $this->uniqueSlug($data['slug'] ?: $data['name'], $attribute->id),
                'is_active' => $data['is_active'] ?? false,
            ]);

            $this->syncValues($attribute, $values);

            return $attribute->load('values');
        });
    }

    public function delete(ProductAttribute $attribute): void
    {
        if ($attribute->values()->whereHas('variants', fn ($query) => $query->withTrashed())->exists()) {
            throw ValidationException::withMessages([
                'attribute' => 'This attribute is used by existing product variations and cannot be deleted.',
            ]);
        }

        $attribute->delete();
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'attribute-' . Str::random(8);
        $slug = $base;
        $suffix = 1;

        while (ProductAttribute::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private function syncValues(ProductAttribute $attribute, array $values): void
    {
        foreach (array_values($values) as $index => $value) {
            $valueData = [
                'label' => trim($value['label']),
                'slug' => Str::slug($value['label']) ?: 'value-' . Str::random(8),
                'color_code' => $attribute->display_type === 'color' ? ($value['color_code'] ?? null) : null,
                'is_active' => $value['is_active'] ?? true,
                'sort_order' => $index,
            ];

            if (! empty($value['id'])) {
                $attributeValue = $attribute->values()->whereKey($value['id'])->first();

                if (! $attributeValue) {
                    throw ValidationException::withMessages([
                        'values' => 'One or more values do not belong to this attribute.',
                    ]);
                }

                if (! $valueData['is_active'] && $attributeValue->variants()->withTrashed()->exists()) {
                    throw ValidationException::withMessages([
                        'values' => "{$attributeValue->label} is used by existing product variations and cannot be deactivated.",
                    ]);
                }

                $attributeValue->update($valueData);
                continue;
            }

            $attribute->values()->updateOrCreate(
                ['slug' => $valueData['slug']],
                $valueData,
            );
        }
    }
}
