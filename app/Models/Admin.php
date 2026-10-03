<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, InteractsWithMedia;

    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Admin has many blogs (Foreign key: admin_id for cascade delete).
     */
    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'admin_id');
    }

    public function assignedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'assigned_agent_id');
    }

    public function orderHistories(): HasMany
    {
        return $this->hasMany(OrderHistory::class, 'admin_id');
    }

    public function verifiedPaymentSubmissions(): HasMany
    {
        return $this->hasMany(PaymentSubmission::class, 'verified_by_admin_id');
    }

    /**
     * Spatie Media Library collection registration
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
        $this->addMediaCollection('avatars')->singleFile();
        $this->addMediaCollection('user_avatar')->singleFile();
        $this->addMediaCollection('profile_photo')->singleFile();
    }

    /**
     * Check if admin has a profile photo.
     */
    public function hasProfilePhoto(): bool
    {
        return $this->hasMedia('avatar')
            || $this->hasMedia('avatars')
            || $this->hasMedia('user_avatar')
            || $this->hasMedia('profile_photo')
            || ! empty($this->avatar_url);
    }

    /**
     * Check if admin account is active.
     */
    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    /**
     * Check if admin is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Agents are resource-scoped: by default they may only work with Orders
     * explicitly assigned to their own admin account.
     */
    public function isRestrictedAgent(): bool
    {
        return $this->hasRole('agent')
            && ! $this->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * AdminLTE navbar user menu avatar render method
     */
    public function adminlte_image(): string
    {
        foreach (['avatar', 'avatars', 'user_avatar', 'profile_photo', 'image'] as $collection) {
            $media = $this->getFirstMedia($collection);
            if (! $media) continue;
            $mediaUrl = $media->getUrl();
            $path = parse_url($mediaUrl, PHP_URL_PATH);
            return $path ? url($path) : $mediaUrl;
        }

        return asset('vendor/adminlte/dist/img/user2-160x160.jpg');
    }

    /**
     * AdminLTE user menu dropdown profile route
     */
    public function adminlte_profile_url(): string
    {
        return route('admin.profile');
    }
}