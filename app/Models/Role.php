<?php

namespace App\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function admins(): MorphToMany
    {
        return $this->morphedByMany(
            Admin::class,
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.role_pivot_key') ?: 'role_id',
            config('permission.column_names.model_morph_key') ?: 'model_id'
        );
    }

    public function isProtected(): bool
    {
        return $this->name === 'super_admin';
    }
}
