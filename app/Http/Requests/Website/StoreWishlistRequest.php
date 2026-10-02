<?php

namespace App\Http\Requests\Website;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn (Builder $query) => $query
                    ->where('status', 'active')
                    ->whereNull('deleted_at')),
            ],
        ];
    }
}
