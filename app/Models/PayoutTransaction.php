<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutTransaction extends Model
{
    protected $fillable = [
        'payout_request_id', 'amount', 'channel', 'transfer_reference', 'proof_path',
        'status', 'processed_by_admin_id', 'processed_at', 'failure_note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function payoutRequest(): BelongsTo
    {
        return $this->belongsTo(PayoutRequest::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'processed_by_admin_id');
    }
}
