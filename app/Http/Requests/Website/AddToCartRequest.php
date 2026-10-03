<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'purchase_mode' => ['nullable', Rule::in(['cart', 'buy_now'])],
            // Kept for backward compatibility with any older form payload.
            'redirect_to' => ['nullable', Rule::in(['back', 'cart', 'checkout'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'quantity' => $this->input('quantity', 1),
            'purchase_mode' => $this->input('purchase_mode', 'cart'),
            'redirect_to' => $this->input('redirect_to', 'back'),
        ]);
    }
}
