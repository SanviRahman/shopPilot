<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreOrderHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = auth('admin')->user();

        if (! $admin?->can('orders.update')) {
            return false;
        }

        $order = Order::query()->find($this->input('order_id'));

        return ! $order || Gate::forUser($admin)->allows('update', $order);
    }

    public function rules(): array
    {
        return [
            'order_id' => [
                'required',
                'integer',
                Rule::exists('orders', 'id')->whereNull('deleted_at'),
            ],
            'note' => ['required', 'string', 'max:2000'],
        ];
    }
}