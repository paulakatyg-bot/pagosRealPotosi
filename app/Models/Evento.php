<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Evento extends Model
{
    use HasUuids;

    protected $fillable = [
        'nombre',
        'fecha',
        'activo'
    ];

    public function socios()
    {
        return $this->belongsToMany(Socio::class, 'socio_eventos')
                    ->withPivot('habilitado')
                    ->withTimestamps();
    }
}
