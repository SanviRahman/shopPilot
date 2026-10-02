<?php

namespace App\Http\Requests\Customer;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('customers.update');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        /** @var User|null $customer */
        $customer = $this->route('customer');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:191', Rule::unique('users', 'email')->ignore($customer?->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
