<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('orders.update');
    }

    public function rules(): array
    {
        return [
            'buyer_name'        => ['required', 'string', 'max:150'],
            'buyer_phone'       => ['required', 'string', 'max:30'],
            'buyer_email'       => ['required', 'email:rfc', 'max:191'],
            'shipping_address'  => ['required', 'string', 'max:1000'],
            'city_or_area'      => ['required', 'string', 'max:150'],
            'subtotal'          => ['required', 'numeric', 'min:0'],
            'discount'          => ['nullable', 'numeric', 'min:0'],
            'shipping'          => ['nullable', 'numeric', 'min:0'],
            'order_status'      => [
                'required',
                Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'failed']),
            ],
            'payment_status'    => [
                'required',
                Rule::in(['unpaid', 'paid', 'partially_paid', 'refunded', 'failed']),
            ],
            'assigned_agent_id' => [
                'nullable',
                'integer',
                Rule::exists('admins', 'id')->whereNull('deleted_at'),
            ],
            'customer_note'     => ['nullable', 'string', 'max:2000'],
            'internal_note'     => ['nullable', 'string', 'max:2000'],
        ];
    }
}