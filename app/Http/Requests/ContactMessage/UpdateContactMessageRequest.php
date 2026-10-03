<?php

namespace App\Http\Requests\ContactMessage;

use App\Models\ContactMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('admin')?->can('contact-messages.update');
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(ContactMessage::STATUSES)]];
    }
}
