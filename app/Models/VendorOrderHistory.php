<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class VendorOrderHistory extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'vendor_order_id', 'admin_id', 'user_id', 'from_status', 'to_status', 'note',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new RuntimeException('Seller order history is immutable.'));
        static::deleting(fn () => throw new RuntimeException('Seller order history cannot be deleted.'));
    }

    public function vendorOrder(): BelongsTo
    {
        return $this->belongsTo(VendorOrder::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
