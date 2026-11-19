<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class VendorFinancialLedger extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'vendor_financial_ledger';

    protected $fillable = [
        'vendor_id', 'vendor_order_id', 'payout_request_id', 'refund_id', 'entry_type',
        'bucket', 'amount', 'currency_code', 'idempotency_key', 'reference_type',
        'reference_id', 'description', 'metadata', 'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'posted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new RuntimeException('Posted vendor ledger entries are immutable.'));
        static::deleting(fn () => throw new RuntimeException('Posted vendor ledger entries cannot be deleted.'));
    }
}
