<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_FIXED = 'fixed';
    public const TYPE_PERCENTAGE = 'percentage';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'minimum_order_amount',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeValidAt(Builder $query, ?Carbon $at = null): Builder
    {
        $at ??= now();
        return $query->active()->where('start_date', '<=', $at)->where('end_date', '>=', $at);
    }

    public function isValidForOrderAmount(float $orderAmount, ?Carbon $at = null): bool
    {
        $at ??= now();

        return $this->status === self::STATUS_ACTIVE
            && $this->start_date?->lte($at)
            && $this->end_date?->gte($at)
            && $orderAmount >= (float) $this->minimum_order_amount;
    }

    public function calculateDiscount(float $orderAmount): float
    {
        if ($orderAmount <= 0) {
            return 0.0;
        }

        $discount = $this->discount_type === self::TYPE_PERCENTAGE
            ? $orderAmount * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        return round(min($discount, $orderAmount), 2);
    }
}
