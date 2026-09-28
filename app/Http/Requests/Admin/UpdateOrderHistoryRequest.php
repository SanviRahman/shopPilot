<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('orders.update');
    }

    public function rules(): array
    {
        return [
            'to_status' => ['nullable', Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'failed'])],
            'note'      => ['required', 'string', 'max:2000'],
        ];
    }
}