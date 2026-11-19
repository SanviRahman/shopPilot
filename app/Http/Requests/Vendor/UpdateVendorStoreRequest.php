<?php

namespace App\Http\Requests\Vendor;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    public function rules(): array
    {
        $store = $this->attributes->get('vendor')?->store;

        return [
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'alpha_dash', 'max:180', Rule::unique('stores', 'slug')->ignore($store?->id)],
            'description' => ['nullable', 'string', 'max:3000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:191'],
            'address' => ['nullable', 'string', 'max:2000'],
            'shipping_policy' => ['nullable', 'string', 'max:5000'],
            'return_policy' => ['nullable', 'string', 'max:5000'],
            'shipping_fee' => ['required', 'numeric', 'min:0', 'max:' . config('shop.marketplace.maximum_vendor_shipping_fee')],
            'vacation_mode' => ['nullable', 'boolean'],
            'store_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'store_banner' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ];
    }
}
