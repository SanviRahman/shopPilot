<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Store extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'vendor_id',
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'address',
        'shipping_policy',
        'return_policy',
        'shipping_fee',
        'status',
        'vacation_mode',
    ];

    protected function casts(): array
    {
        return ['vacation_mode' => 'boolean', 'shipping_fee' => 'decimal:2'];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('store_logo')->singleFile();
        $this->addMediaCollection('store_banner')->singleFile();
    }
}
