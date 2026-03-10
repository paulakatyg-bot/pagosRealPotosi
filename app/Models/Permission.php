<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Traits\HasUuids;

class Permission extends SpatiePermission
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
}