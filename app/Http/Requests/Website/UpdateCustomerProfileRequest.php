<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/\D+/', '', (string) $this->input('phone_number')) ?: '';
        if (str_starts_with($phone, '880')) {
            $phone = substr($phone, 3);
        }
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }
        $phone = $phone !== '' ? '+880' . $phone : null;

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone_number' => $phone,
            'division' => trim((string) $this->input('division')),
            'district' => trim((string) $this->input('district')),
            'upazila' => trim((string) $this->input('upazila')),
            'address' => trim((string) $this->input('address')),
        ]);
    }

    public function rules(): array
    {
        $userId = $this->user('web')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:191', Rule::unique('users', 'email')->ignore($userId)],
            'phone_number' => ['nullable', 'string', 'max:30', 'regex:/^\+8801[3-9][0-9]{8}$/'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'date_of_birth' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'division' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'upazila' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Enter a valid Bangladesh mobile number.',
            'avatar.max' => 'Profile photo must be 2 MB or smaller.',
        ];
    }
}
