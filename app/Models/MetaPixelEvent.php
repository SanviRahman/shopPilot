<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaPixelEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'session_id', 'event_name', 'event_id', 'page_url', 'referrer',
        'pixel_ids', 'payload', 'delivery_status', 'ip_hash', 'user_agent', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['pixel_ids' => 'array', 'payload' => 'array', 'occurred_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
