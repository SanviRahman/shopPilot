<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyCartCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coupon_code' => ['required', 'string', 'max:80'],
            'checkout_mode' => ['nullable', Rule::in(['cart', 'buy_now'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('coupon_code')) {
            $this->merge([
                'coupon_code' => strtoupper(trim((string) $this->input('coupon_code'))),
            ]);
        }
    }
}
