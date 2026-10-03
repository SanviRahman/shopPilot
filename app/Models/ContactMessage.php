<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['new', 'read', 'resolved'];

    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'status', 'ip_address', 'user_agent', 'read_at', 'resolved_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime', 'resolved_at' => 'datetime'];
    }
}
