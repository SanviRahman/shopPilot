<?php

namespace App\Http\Requests\Contact;

class UpdateContactRequest extends StoreContactRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('contacts.update');
    }
}
