<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerPaymentSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'transaction_id' => strtoupper(trim((string) $this->input('transaction_id'))),
        ]);
    }

    public function rules(): array
    {
        $paymentCodes = (array) config('shop.checkout.manual_payment_codes', []);

        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('payment_methods', 'id')->where(function ($query) use ($paymentCodes) {
                    $query->where('status', 'active')
                        ->whereNull('deleted_at')
                        ->whereIn('code', $paymentCodes);
                }),
            ],
            'transaction_id' => [
                'required',
                'string',
                'min:5',
                'max:100',
                'regex:/^[A-Z0-9][A-Z0-9._-]{4,99}$/i',
                Rule::unique('payment_submissions', 'transaction_id')->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method_id.exists' => 'Please select an active manual payment method.',
            'transaction_id.regex' => 'Enter a valid transaction ID using letters, numbers, dots, dashes or underscores.',
            'transaction_id.unique' => 'This transaction ID has already been submitted.',
        ];
    }
}
