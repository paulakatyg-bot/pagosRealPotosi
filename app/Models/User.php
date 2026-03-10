<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Trait nativo

class User extends Authenticatable
{
    use Notifiable, HasRoles, HasUuids;

    protected $fillable = [
        'id', 
        'name',
        'email',
        'password',
        'cargo_id', 
    ];

    // Relación con Cargo
    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }
}