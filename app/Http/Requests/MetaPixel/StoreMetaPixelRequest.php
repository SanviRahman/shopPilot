<?php

namespace App\Http\Requests\MetaPixel;

use App\Models\MetaPixel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMetaPixelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('meta-pixels.create');
    }

    protected function prepareForValidation(): void
    {
        $ids = preg_split('/[\s,]+/', trim((string) $this->input('pixel_ids_text')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'pixel_ids' => array_values(array_unique($ids)),
            'track_page_view' => $this->boolean('track_page_view'),
            'track_ecommerce' => $this->boolean('track_ecommerce'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'pixel_ids' => ['required', 'array', 'min:1', 'max:20'],
            'pixel_ids.*' => ['required', 'string', 'regex:/^[0-9]{5,30}$/'],
            'full_script' => ['nullable', 'string', 'max:200000'],
            'lifecycle_status' => ['required', Rule::in(MetaPixel::LIFECYCLE)],
            'track_page_view' => ['boolean'],
            'track_ecommerce' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
