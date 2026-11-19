<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayoutRequest extends Model
{
    protected $fillable = [
        'vendor_id', 'payout_account_id', 'amount', 'currency_code', 'status',
        'requested_at', 'reviewed_by_admin_id', 'reviewed_at', 'review_note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class)->withTrashed();
    }

    public function payoutAccount(): BelongsTo
    {
        return $this->belongsTo(VendorPayoutAccount::class, 'payout_account_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PayoutTransaction::class);
    }
}
