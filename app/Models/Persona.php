<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuids;

class Persona extends Model
{
    use HasUuids;

    protected $fillable = ['cargo_id', 'nombre', 'ci', 'telefono','posicion','nacionalidad'];

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function cuentasFinancieras() // <--- camelCase
    {
        return $this->hasMany(CuentaFinanciera::class);
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
    public function tarjetaAbono()
    {
        return $this->hasOne(TarjetaAbono::class, 'persona_id');
    }
}