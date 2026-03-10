<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Traits\HasUuids;

class Role extends SpatieRole
{
    use HasUuids;

    // Forzamos a que reconozca que el ID es un string (UUID)
    protected $keyType = 'string';
    public $incrementing = false;
}