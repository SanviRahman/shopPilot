<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('products.update');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['product_type' => $this->input('product_type', 'simple')]);
    }

    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');
        $simple = $this->input('product_type') === 'simple';
        $variable = $this->input('product_type') === 'variable';

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('products', 'slug')->ignore($product?->id)],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product?->id), 'unique:product_variants,sku'],
            'product_type' => ['required', Rule::in(['simple', 'variable'])],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'regular_price' => [Rule::requiredIf($simple), 'nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', Rule::when($simple, ['lt:regular_price'])],
            'stock_quantity' => [Rule::requiredIf($simple), 'nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'featured' => ['nullable', 'boolean'],

            'variants' => [Rule::requiredIf($variable), 'nullable', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.sku' => ['required_if:product_type,variable', 'string', 'max:100', 'distinct'],
            'variants.*.regular_price' => ['required_if:product_type,variable', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['required_if:product_type,variable', 'integer', 'min:0'],
            'variants.*.attribute_value_ids' => ['required_if:product_type,variable', 'array', 'min:1'],
            'variants.*.attribute_value_ids.*' => ['required', 'integer', 'exists:product_attribute_values,id'],
            'variants.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],

            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'og_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string', 'max:500'],
            'twitter_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'twitter_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'remove_image' => ['nullable', 'boolean'],
            'remove_og_image' => ['nullable', 'boolean'],
            'remove_twitter_image' => ['nullable', 'boolean'],
        ];
    }
}
