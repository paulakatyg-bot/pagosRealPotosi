<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuids;

class CuentaFinanciera extends Model
{
    use HasUuids;

    protected $table = 'cuentas_financieras';

    protected $fillable = [
        'persona_id', 
        'banco_id', 
        'identificador_cuenta', 
        'observacion_cuenta'
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function banco()
    {
        return $this->belongsTo(Banco::class);
    }
}