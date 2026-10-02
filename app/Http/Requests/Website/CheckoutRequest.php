<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'buyer_name' => trim((string) $this->input('buyer_name')),
            'buyer_phone' => preg_replace('/\s+/', '', trim((string) $this->input('buyer_phone'))),
            'buyer_email' => strtolower(trim((string) $this->input('buyer_email'))),
            'shipping_address' => trim((string) $this->input('shipping_address')),
            'division' => trim((string) $this->input('division')),
            'district' => trim((string) $this->input('district')),
            'upazila' => trim((string) $this->input('upazila')),
            'postal_code' => trim((string) $this->input('postal_code')),
            'customer_note' => trim((string) $this->input('customer_note')),
            'transaction_id' => strtoupper(trim((string) $this->input('transaction_id'))),
        ]);
    }

    public function rules(): array
    {
        $shippingMethods = array_keys((array) config('shop.checkout.shipping_methods', []));
        $paymentCodes = (array) config('shop.checkout.manual_payment_codes', []);

        return [
            'buyer_name' => ['required', 'string', 'max:150'],
            'buyer_phone' => ['required', 'string', 'max:30', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'buyer_email' => ['required', 'email:rfc', 'max:191'],
            'address_type' => ['nullable', Rule::in(['home', 'office'])],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'division' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'upazila' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'delivery_method' => ['required', Rule::in($shippingMethods)],
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
            'buyer_phone.regex' => 'Enter a valid Bangladesh mobile number, for example 017XXXXXXXX.',
            'payment_method_id.exists' => 'Please select an active manual payment method.',
            'transaction_id.regex' => 'Enter a valid transaction ID using letters, numbers, dots, dashes or underscores.',
            'transaction_id.unique' => 'This transaction ID has already been submitted.',
        ];
    }
}
