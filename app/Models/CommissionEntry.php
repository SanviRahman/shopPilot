<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class CommissionEntry extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'vendor_order_id', 'commission_rule_id', 'basis_amount', 'rate_value',
        'commission_amount', 'vendor_earning', 'currency_code', 'rule_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'basis_amount' => 'decimal:2',
            'rate_value' => 'decimal:4',
            'commission_amount' => 'decimal:2',
            'vendor_earning' => 'decimal:2',
            'rule_snapshot' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new RuntimeException('Commission snapshots are immutable.'));
        static::deleting(fn () => throw new RuntimeException('Commission snapshots cannot be deleted.'));
    }

    public function vendorOrder(): BelongsTo
    {
        return $this->belongsTo(VendorOrder::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class, 'commission_rule_id');
    }
}
