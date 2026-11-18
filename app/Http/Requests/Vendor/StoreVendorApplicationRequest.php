<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVendorApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:180'],
            'legal_name' => ['nullable', 'string', 'max:180'],
            'business_type' => ['nullable', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['required', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'store_name' => ['required', 'string', 'max:180'],
            'store_slug' => ['nullable', 'alpha_dash', 'max:180', Rule::unique('stores', 'slug')],
            'store_description' => ['nullable', 'string', 'max:3000'],
            'document_type' => ['required', 'string', 'max:100'],
            'documents' => ['required', 'array', 'min:1', 'max:5'],
            'documents.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
