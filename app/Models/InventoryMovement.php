<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_balance_id',
        'product_id',
        'product_variant_id',
        'created_by_user_id',
        'movement_type',
        'quantity_change',
        'on_hand_before',
        'on_hand_after',
        'reference_type',
        'reference_id',
        'note',
        'created_by_admin_id',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'on_hand_before' => 'integer',
        'on_hand_after' => 'integer',
    ];

    public function inventoryBalance(): BelongsTo
    {
        return $this->belongsTo(InventoryBalance::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id')->withTrashed();
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id')->withTrashed();
    }
}
