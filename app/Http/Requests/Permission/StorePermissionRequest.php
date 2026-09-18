<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('permissions.manage');
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255', 'regex:/^[a-z0-9._-]+$/',
                Rule::unique('permissions', 'name')->where(fn ($query) => $query->where('guard_name', 'admin')->whereNull('deleted_at')),
            ],
            'guard_name' => ['required', Rule::in(['admin'])],
            'group_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
