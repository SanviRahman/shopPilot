<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'uploaded_by_user_id',
        'reviewed_by_admin_id',
        'document_type',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'file_size',
        'status',
        'rejection_reason',
        'expires_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'expires_at' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id')->withTrashed();
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id')->withTrashed();
    }
}
