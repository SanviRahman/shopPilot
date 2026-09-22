<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
     * AdminLTE navbar user menu avatar render method
     */
    public function adminlte_image(): string
    {
        $mediaUrl = $this->getFirstMediaUrl('avatar')
            ?: $this->getFirstMediaUrl('avatars')
            ?: $this->getFirstMediaUrl('user_avatar')
            ?: $this->getFirstMediaUrl('profile_photo')
            ?: $this->getFirstMediaUrl('image');

        if (! empty($mediaUrl)) {
            return $mediaUrl;
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'Admin') . '&background=007bff&color=ffffff&bold=true&rounded=true';
    }

    /**
     * AdminLTE user menu dropdown profile route
     */
    public function adminlte_profile_url(): string
    {
        return route('admin.profile');
    }
}
