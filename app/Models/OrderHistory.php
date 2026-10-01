<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderHistory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The canonical order_histories table is append-oriented and has no updated_at column.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'admin_id',
        'user_id',
        'from_status',
        'to_status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function getActorBadgeAttribute(): string
    {
        if ($this->admin_id && $this->admin) {
            return '<span class="badge badge-primary px-2 py-1"><i class="fas fa-user-shield mr-1"></i>Staff: ' . e($this->admin->name) . '</span>';
        }

        if ($this->user_id && $this->user) {
            return '<span class="badge badge-info px-2 py-1"><i class="fas fa-user mr-1"></i>Buyer: ' . e($this->user->name) . '</span>';
        }

        return '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-robot mr-1"></i>System / Guest</span>';
    }

    public function getStatusTransitionBadgeAttribute(): string
    {
        if (! $this->from_status && ! $this->to_status) {
            return '<span class="badge badge-light border text-muted">Event / Note</span>';
        }

        $from = $this->from_status ? ucfirst($this->from_status) : 'Start';
        $to = $this->to_status ? ucfirst($this->to_status) : 'N/A';

        return sprintf(
            '<span class="badge badge-light border text-secondary">%s</span> <i class="fas fa-arrow-right text-muted mx-1" style="font-size:10px;"></i> <span class="badge badge-success">%s</span>',
            e($from),
            e($to),
        );
    }
}
