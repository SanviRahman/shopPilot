<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'password',
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

    public function blogs()
    {
        return $this->morphMany(\App\Models\Blog::class, 'author');
    }

    /**
     * Spatie Media Library collection registration
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
        $this->addMediaCollection('user_avatar')->singleFile();
    }

    /**
     * AdminLTE navbar user menu avatar render korar method
     */
    public function adminlte_image(): string
    {
        $mediaUrl = $this->getFirstMediaUrl('avatar')
            ?: $this->getFirstMediaUrl('user_avatar')
            ?: $this->getFirstMediaUrl('profile_photo');

        if (! empty($mediaUrl)) {
            return $mediaUrl;
        }

        // Image na thakle dynamic rounded avatar fallback
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'Admin') . '&background=007bff&color=ffffff&bold=true&rounded=true';
    }

    /**
     * AdminLTE user menu dropdown profile route
     */
    public function adminlte_profile_url(): string
    {
        return route('admin.profile');
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(['admin', 'manager', 'agent']);
    }

    public function scopeStaff(Builder $query): Builder
    {
        return $query->role(['admin', 'manager', 'agent']);
    }

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->role('customer');
    }
}
