<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRule extends Model
{
    protected $fillable = [
        'scope_type', 'rate_value', 'commission_basis', 'effective_from',
        'effective_to', 'is_active', 'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'rate_value' => 'decimal:4',
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public static function activeMarketplaceRule(bool $lockForUpdate = false): self
    {
        $query = static::query()
            ->where('scope_type', 'marketplace')
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhere('effective_from', '<=', now()))
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>', now()))
            ->latest('effective_from');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->firstOrFail();
    }
}
