<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SocioEvento extends Model
{
    use HasUuids;

    protected $table = 'socio_eventos';

    protected $fillable = [
        'socio_id',
        'evento_id',
        'habilitado'
    ];
}
