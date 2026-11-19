<?php

namespace App\Http\Requests\ProductAttribute;

use App\Models\ProductAttribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveProductAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('product_attribute') ? 'product-attributes.update' : 'product-attributes.create';

        return (bool) auth('admin')->user()?->can($permission);
    }

    public function rules(): array
    {
        /** @var ProductAttribute|null $attribute */
        $attribute = $this->route('product_attribute');

        return [
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'string', 'max:100', Rule::unique('product_attributes', 'slug')->ignore($attribute?->id)],
            'display_type' => ['required', Rule::in(['select', 'button', 'color'])],
            'is_active' => ['nullable', 'boolean'],
            'values' => ['required', 'array', 'min:1'],
            'values.*.id' => ['nullable', 'integer', 'exists:product_attribute_values,id'],
            'values.*.label' => ['required', 'string', 'max:80', 'distinct'],
            'values.*.color_code' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'values.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
