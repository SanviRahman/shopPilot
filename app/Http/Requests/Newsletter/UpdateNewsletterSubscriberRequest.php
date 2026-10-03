<?php

namespace App\Http\Requests\Newsletter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check() && auth('admin')->user()?->can('newsletter-subscribers.update');
    }

    public function rules(): array
    {
        $subscriber = $this->route('newsletter_subscriber');
        return ['email' => ['required', 'email:rfc', 'max:191', Rule::unique('newsletter_subscribers', 'email')->ignore($subscriber?->getKey())], 'status' => ['required', 'in:subscribed,unsubscribed']];
    }
}
