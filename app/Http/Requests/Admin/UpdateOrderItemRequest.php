<?php

namespace App\Http\Requests\Admin;

use App\Models\OrderItem;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = auth('admin')->user();
        $routeOrderItem = $this->route('orderItem') ?? $this->route('order_item');
        $orderItem = $routeOrderItem instanceof OrderItem
            ? $routeOrderItem
            : OrderItem::query()->find($routeOrderItem);

        if (! $admin?->can('orders.update')) {
            return false;
        }

        if (! $orderItem) {
            return true;
        }

        $orderItem->loadMissing('order');

        return $orderItem->order !== null
            && Gate::forUser($admin)->allows('update', $orderItem->order);
    }

    public function rules(): array
    {
        $routeOrderItem = $this->route('orderItem') ?? $this->route('order_item');
        $orderItem = $routeOrderItem instanceof OrderItem
            ? $routeOrderItem
            : OrderItem::query()->find($routeOrderItem);
        $currentProductId = (int) ($orderItem?->product_id ?? 0);

        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(
                    fn (Builder $query) => $query
                        ->whereNull('deleted_at')
                        ->orWhere('id', $currentProductId)
                ),
            ],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }
}
