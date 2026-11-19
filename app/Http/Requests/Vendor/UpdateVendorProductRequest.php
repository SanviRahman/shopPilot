<?php

namespace App\Http\Requests\Vendor;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['product_type' => $this->input('product_type', 'simple')]);
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : null;
        $simple = $this->input('product_type') === 'simple';
        $variable = $this->input('product_type') === 'variable';

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('status', 'active')],
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId), 'unique:product_variants,sku'],
            'product_type' => ['required', Rule::in(['simple', 'variable'])],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'regular_price' => [Rule::requiredIf($simple), 'nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', Rule::when($simple, ['lt:regular_price'])],
            'stock_quantity' => [Rule::requiredIf($simple), 'nullable', 'integer', 'min:0'],
            'variants' => [Rule::requiredIf($variable), 'nullable', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['required_if:product_type,variable', 'string', 'max:100', 'distinct'],
            'variants.*.regular_price' => ['required_if:product_type,variable', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['required_if:product_type,variable', 'integer', 'min:0'],
            'variants.*.attribute_value_ids' => ['required_if:product_type,variable', 'array', 'min:1'],
            'variants.*.attribute_value_ids.*' => ['required', 'integer', 'exists:product_attribute_values,id'],
            'variants.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }
}
