<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuids; // IMPORTANTE: Verificar que esta línea existe

class Cargo extends Model
{
    use HasUuids; // IMPORTANTE: Verificar que esta línea existe

    protected $fillable = ['nombre'];

    // Forzamos a Laravel a entender que el ID es un string y no se autoincrementa
    public $incrementing = false;
    protected $keyType = 'string';

    public function personas()
    {
        return $this->hasMany(Persona::class);
    }
}