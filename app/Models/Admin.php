<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaModel;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements HasMedia
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    use InteractsWithMedia;

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
            'password' => 'hashed',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')->singleFile();
    }

    public function registerMediaConversions(?MediaModel $media = null): void
    {
        $this->addMediaConversion('thumb')->width(150)->height(150)->sharpen(10);
    }

    public function hasProfilePhoto(): bool
    {
        return $this->hasMedia('avatars');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('avatars')
            ?: asset('vendor/adminlte/dist/img/user2-160x160.jpg');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }
}