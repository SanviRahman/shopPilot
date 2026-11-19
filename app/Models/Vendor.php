<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'business_name',
        'legal_name',
        'business_type',
        'email',
        'phone',
        'website',
        'tax_identifier',
        'address',
        'city',
        'country',
        'status',
        'reviewed_by_admin_id',
        'reviewed_at',
        'approved_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vendor_users')
            ->withPivot(['role', 'status', 'permissions', 'joined_at'])
            ->withTimestamps();
    }

    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner')->wherePivot('status', 'active');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(VendorStatusHistory::class)->latest('created_at');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(VendorOrder::class);
    }

    public function payoutAccounts(): HasMany
    {
        return $this->hasMany(VendorPayoutAccount::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function financialEntries(): HasMany
    {
        return $this->hasMany(VendorFinancialLedger::class);
    }

    public function canTransitionTo(string $status): bool
    {
        $transitions = [
            self::STATUS_PENDING_REVIEW => [self::STATUS_APPROVED, self::STATUS_REJECTED],
            self::STATUS_REJECTED => [self::STATUS_PENDING_REVIEW],
            self::STATUS_APPROVED => [self::STATUS_SUSPENDED, self::STATUS_CLOSED],
            self::STATUS_SUSPENDED => [self::STATUS_APPROVED, self::STATUS_CLOSED],
            self::STATUS_CLOSED => [],
        ];

        return in_array($status, $transitions[$this->status] ?? [], true);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
    public function vendorOrders(): HasMany
    {
        return $this->hasMany(VendorOrder::class);
    }

    public function financialLedger(): HasMany
    {
        return $this->hasMany(VendorFinancialLedger::class);
    }

}
