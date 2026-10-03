<?php

namespace App\Http\Requests\Newsletter;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check() && auth('admin')->user()?->can('newsletter-subscribers.create');
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email:rfc', 'max:191'], 'status' => ['required', 'in:subscribed,unsubscribed']];
    }
}
