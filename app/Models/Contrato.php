<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuids;

class Contrato extends Model
{
    use HasUuids;

    protected $fillable = [
        'persona_id', 
        'monto_mensual', 
        'moneda', 
        'fecha_inicio', 
        'fecha_fin', 
        'modalidad'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'monto_mensual' => 'decimal:2'
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
    public function primas() {
        return $this->hasMany(Prima::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function saldoSueldoMes($mes) {
        $mensualidad = $this->monto_mensual;
        $pagado = $this->pagos()
            ->where('mes_correspondiente', $mes)
            ->whereIn('tipo_pago', ['Sueldo', 'Anticipo Sueldo'])
            ->sum('debe_equivalente');
            
        return $mensualidad - $pagado;
    }
}