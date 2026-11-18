<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vendor_id',
        'changed_by_admin_id',
        'changed_by_user_id',
        'from_status',
        'to_status',
        'reason',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function changedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'changed_by_admin_id')->withTrashed();
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id')->withTrashed();
    }
}
