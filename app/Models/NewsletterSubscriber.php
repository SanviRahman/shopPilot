<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsletterSubscriber extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['subscribed', 'unsubscribed'];

    protected $fillable = ['email', 'status', 'subscribed_at', 'unsubscribed_at'];

    protected function casts(): array
    {
        return ['subscribed_at' => 'datetime', 'unsubscribed_at' => 'datetime'];
    }
}
