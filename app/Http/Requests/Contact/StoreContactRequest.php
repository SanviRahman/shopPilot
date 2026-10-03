<?php

namespace App\Http\Requests\Contact;

use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('contacts.create');
    }

    protected function prepareForValidation(): void
    {
        $mapUrl = trim(html_entity_decode((string) $this->input('map_url'), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if (preg_match('~<iframe\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1~is', $mapUrl, $match)) {
            $mapUrl = trim(html_entity_decode((string) $match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => trim((string) $this->input('phone')),
            'map_url' => $mapUrl,
            'status' => $this->input('status', 'active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:191'],
            'phone' => ['required', 'string', 'max:50', 'regex:/^[0-9+()\-\.\s]{6,50}$/'],
            // Store only a URL. Never accept/render raw iframe or script HTML from this field.
            'map_url' => ['required', 'string', 'max:2048', 'url:http,https'],
            'status' => ['required', Rule::in(Contact::STATUSES)],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid phone number using digits and standard phone symbols.',
            'map_url.url' => 'Enter a valid http:// or https:// map URL.',
        ];
    }
}
