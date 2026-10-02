<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class TrackOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_number' => strtoupper(trim((string) $this->input('order_number'))),
            'contact' => trim((string) $this->input('contact')),
        ]);
    }

    public function rules(): array
    {
        return [
            'order_number' => ['required', 'string', 'max:40'],
            'contact' => ['required', 'string', 'max:191'],
        ];
    }

    public function attributes(): array
    {
        return [
            'contact' => 'email or phone number',
        ];
    }
}
