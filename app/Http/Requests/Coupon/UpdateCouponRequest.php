<?php

namespace App\Http\Requests\Coupon;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->user()?->can('coupons.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'minimum_order_amount' => $this->filled('minimum_order_amount')
                ? $this->input('minimum_order_amount')
                : 0,
        ]);
    }

    public function rules(): array
    {
        $coupon = $this->route('coupon');

        return [
            'code' => [
                'required',
                'string',
                'max:80',
                Rule::unique('coupons', 'code')->ignore($coupon),
            ],
            'discount_type' => ['required', Rule::in([Coupon::TYPE_FIXED, Coupon::TYPE_PERCENTAGE])],
            'discount_value' => [
                'required',
                'numeric',
                'gt:0',
                Rule::when(
                    $this->input('discount_type') === Coupon::TYPE_PERCENTAGE,
                    ['max:100']
                ),
            ],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', Rule::in([
                Coupon::STATUS_ACTIVE,
                Coupon::STATUS_INACTIVE,
                Coupon::STATUS_EXPIRED,
            ])],
        ];
    }

    public function messages(): array
    {
        return [
            'discount_value.max' => 'Percentage discount cannot be greater than 100%.',
            'end_date.after' => 'End date must be after the start date.',
        ];
    }
}
