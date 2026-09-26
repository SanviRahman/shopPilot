<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

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

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'shipping'    => 'decimal:2',
        'grand_total' => 'decimal:2',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_agent_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isGuest(): bool
    {
        return is_null($this->user_id);
    }

    public function getOrderStatusBadgeAttribute(): string
    {
        return match ($this->order_status) {
            'pending'    => '<span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i>Pending</span>',
            'processing' => '<span class="badge badge-info px-2 py-1"><i class="fas fa-spinner fa-spin mr-1"></i>Processing</span>',
            'shipped'    => '<span class="badge badge-primary px-2 py-1"><i class="fas fa-shipping-fast mr-1"></i>Shipped</span>',
            'delivered'  => '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Delivered</span>',
            'cancelled'  => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-ban mr-1"></i>Cancelled</span>',
            'refunded'   => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-undo mr-1"></i>Refunded</span>',
            default      => '<span class="badge badge-dark px-2 py-1">' . ucfirst($this->order_status) . '</span>',
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid'           => '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Paid</span>',
            'unpaid'         => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Unpaid</span>',
            'partially_paid' => '<span class="badge badge-info px-2 py-1"><i class="fas fa-adjust mr-1"></i>Partially Paid</span>',
            'refunded'       => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-undo mr-1"></i>Refunded</span>',
            default          => '<span class="badge badge-dark px-2 py-1">' . ucfirst($this->payment_status) . '</span>',
        };
    }
}
