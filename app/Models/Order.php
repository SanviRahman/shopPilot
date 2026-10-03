<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_SUBMITTED = 'submitted';
    public const PAYMENT_VERIFIED = 'verified';
    public const PAYMENT_REJECTED = 'rejected';

    public const ORDER_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    public const PAYMENT_STATUSES = [
        self::PAYMENT_UNPAID,
        self::PAYMENT_SUBMITTED,
        self::PAYMENT_VERIFIED,
        self::PAYMENT_REJECTED,
    ];

    /** @var array<string, list<string>> */
    private const STATUS_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
        self::STATUS_SHIPPED => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED => [],
        self::STATUS_CANCELLED => [],
    ];

    protected $fillable = [
        'order_number',
        'user_id',
        'assigned_agent_id',
        'coupon_id',
        'coupon_code',
        'buyer_name',
        'buyer_phone',
        'buyer_email',
        'shipping_address',
        'city_or_area',
        'subtotal',
        'discount',
        'shipping',
        'grand_total',
        'payment_status',
        'order_status',
        'customer_note',
        'internal_note',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_agent_id')->withTrashed();
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function paymentSubmission(): HasOne
    {
        return $this->hasOne(PaymentSubmission::class)->withTrashed();
    }

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Scope staff Order queries so operational Agents only see assigned Orders.
     */
    public function scopeAccessibleToAdmin(Builder $query, Admin $admin): Builder
    {
        if ($admin->isRestrictedAgent()) {
            $query->where('assigned_agent_id', $admin->getKey());
        }

        return $query;
    }

    public function canTransitionTo(string $status): bool
    {
        if ($status === $this->order_status) {
            return true;
        }

        return in_array(
            $status,
            self::STATUS_TRANSITIONS[$this->order_status] ?? [],
            true,
        );
    }

    /** @return list<string> */
    public function allowedNextStatuses(): array
    {
        return self::STATUS_TRANSITIONS[$this->order_status] ?? [];
    }

    public function getOrderStatusBadgeAttribute(): string
    {
        return match ($this->order_status) {
            self::STATUS_PENDING => '<span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i>Pending</span>',
            self::STATUS_CONFIRMED => '<span class="badge badge-info px-2 py-1"><i class="fas fa-check mr-1"></i>Confirmed</span>',
            self::STATUS_PROCESSING => '<span class="badge badge-primary px-2 py-1"><i class="fas fa-spinner mr-1"></i>Processing</span>',
            self::STATUS_SHIPPED => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-shipping-fast mr-1"></i>Shipped</span>',
            self::STATUS_DELIVERED => '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Delivered</span>',
            self::STATUS_CANCELLED => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-ban mr-1"></i>Cancelled</span>',
            default => '<span class="badge badge-dark px-2 py-1">' . e(ucfirst((string) $this->order_status)) . '</span>',
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_UNPAID => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-wallet mr-1"></i>Unpaid</span>',
            self::PAYMENT_SUBMITTED => '<span class="badge badge-warning px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i>Submitted</span>',
            self::PAYMENT_VERIFIED => '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Verified</span>',
            self::PAYMENT_REJECTED => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Rejected</span>',
            default => '<span class="badge badge-dark px-2 py-1">' . e(ucfirst((string) $this->payment_status)) . '</span>',
        };
    }
}
