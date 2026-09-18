<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
{

    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('staff.update');
    }

    public function rules(): array
    {
        /** @var Admin $admin */
        $admin = $this->route('admin');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email:rfc',
                'max:191',
                Rule::unique('admins', 'email')->ignore($admin?->getKey()),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                'integer',
                Rule::exists('roles', 'id')->where(
                    fn ($query) => $query->where('guard_name', 'admin')->whereNull('deleted_at')
                ),
            ],
        ];
    }
}
