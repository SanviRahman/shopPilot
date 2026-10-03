<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class ContactService
{
    public function create(array $data): Contact
    {
        return DB::transaction(fn (): Contact => Contact::create($data));
    }

    public function update(Contact $contact, array $data): Contact
    {
        return DB::transaction(function () use ($contact, $data): Contact {
            $contact->update($data);

            return $contact->refresh();
        });
    }

    public function delete(Contact $contact): void
    {
        $contact->delete();
    }

    public function restore(Contact $contact): void
    {
        $contact->restore();
    }

    public function forceDelete(Contact $contact): void
    {
        $contact->forceDelete();
    }
}
