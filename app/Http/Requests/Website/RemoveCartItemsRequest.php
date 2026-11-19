<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class RemoveCartItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_keys' => ['required_without:product_ids', 'array', 'min:1'],
            'item_keys.*' => ['required', 'string', 'distinct'],
            'product_ids' => ['required_without:item_keys', 'array', 'min:1'],
            'product_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
