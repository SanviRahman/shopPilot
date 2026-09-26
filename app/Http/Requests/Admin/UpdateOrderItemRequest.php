<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('orders.update');
    }

    public function rules(): array
    {
        return [
            'product_name' => ['required', 'string', 'max:255'],
            'variant_name' => ['nullable', 'string', 'max:255'],
            'sku'          => ['nullable', 'string', 'max:100'],
            'unit_price'   => ['required', 'numeric', 'min:0'],
            'quantity'     => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }
}