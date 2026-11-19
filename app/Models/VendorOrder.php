<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorOrder extends Model
{
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY_TO_SHIP = 'ready_to_ship';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id', 'vendor_id', 'seller_key', 'suborder_number', 'subtotal', 'discount',
        'shipping_fee', 'grand_total', 'commission_rate', 'commission_basis_amount',
        'commission_amount', 'vendor_earning', 'currency_code', 'payment_status', 'status',
        'shipping_provider', 'tracking_number', 'tracking_url', 'shipped_at', 'delivered_at',
        'cancelled_at', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'commission_rate' => 'decimal:4',
            'commission_basis_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'vendor_earning' => 'decimal:2',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function commissionEntry(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CommissionEntry::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(VendorOrderHistory::class)->orderBy('created_at');
    }

    public function canTransitionTo(string $status): bool
    {
        $transitions = [
            self::STATUS_AWAITING_PAYMENT => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
            self::STATUS_CONFIRMED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING => [self::STATUS_READY_TO_SHIP, self::STATUS_CANCELLED],
            self::STATUS_READY_TO_SHIP => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
            self::STATUS_SHIPPED => [self::STATUS_DELIVERED],
            self::STATUS_DELIVERED => [],
            self::STATUS_CANCELLED => [],
        ];

        return $status === $this->status || in_array($status, $transitions[$this->status] ?? [], true);
    }
}
