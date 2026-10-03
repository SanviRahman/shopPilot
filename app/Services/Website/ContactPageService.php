<?php

namespace App\Services\Website;

use App\Models\Contact;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\DB;

class ContactPageService
{
    public function primaryContact(): ?Contact
    {
        return Contact::query()->active()->latest('id')->first();
    }

    public function storeMessage(array $data, ?string $ipAddress = null, ?string $userAgent = null): ContactMessage
    {
        unset($data['company']);

        return DB::transaction(function () use ($data, $ipAddress, $userAgent): ContactMessage {
            return ContactMessage::create([...$data, 'status' => 'new', 'ip_address' => $ipAddress, 'user_agent' => $userAgent ? mb_substr($userAgent, 0, 500) : null]);
        });
    }
}
