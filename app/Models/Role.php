<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function isSystem(): bool
    {
        return in_array($this->name, ['Admin', 'Manager', 'Agent', 'Customer'], true);
    }
}