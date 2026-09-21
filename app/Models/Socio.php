<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Socio extends Model
{
     use HasUuids;

    protected $fillable = [
        'persona_id',
        'codigo_socio',
        'tipo',
        'estado'
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function eventos()
    {
        return $this->belongsToMany(Evento::class, 'socio_eventos')
                    ->withPivot('habilitado')
                    ->withTimestamps();
    }
}
