<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Exception;

class Pago extends Model
{
    use HasUuids;

    protected $fillable = [
        'persona_id', 
        'contrato_id', 
        'prima_id',
        'comprobante_fise', // Vínculo con sistema contable externo
        'tipo_pago', 
        'monto_pagado', 
        'moneda_pago', 
        'tipo_cambio', 
        'debe_equivalente',   // Lo que se reconoce como pagado (Moneda Contrato)
        'haber_equivalente',  // Lo que efectivamente salió (Moneda Contrato)
        'fecha_operacion',
        'mes_correspondiente',
        'observacion'
    ];

    protected $casts = [
        'fecha_operacion'   => 'date',
        'monto_pagado'      => 'decimal:2',
        'debe_equivalente'  => 'decimal:2',
        'haber_equivalente' => 'decimal:2',
        'tipo_cambio'       => 'decimal:6',
    ];

    /**
     * Boot del modelo para validaciones rigurosas antes de insertar.
     */
    protected static function booted()
    {
        static::creating(function ($pago) {
            $pago->validarIntegridadContable();
        });
    }

    /**
     * Lógica de validación para evitar pagos en exceso.
     */
    public function validarIntegridadContable()
    {
        // 1. Validar que si es sueldo, tenga el mes obligatorio
        if (in_array($this->tipo_pago, ['Sueldo', 'Anticipo Sueldo']) && !$this->mes_correspondiente) {
            throw new Exception("Error Contable: El mes correspondiente es obligatorio para sueldos/anticipos.");
        }

        // 2. Control de Sueldo Mensual (No pagar más de lo que dice el contrato para ese mes)
        if (in_array($this->tipo_pago, ['Sueldo', 'Anticipo Sueldo'])) {
            $contrato = Contrato::find($this->contrato_id);
            $yaPagado = Pago::where('contrato_id', $this->contrato_id)
                ->where('mes_correspondiente', $this->mes_correspondiente)
                ->whereIn('tipo_pago', ['Sueldo', 'Anticipo Sueldo'])
                ->sum('debe_equivalente');

            if (($yaPagado + $this->debe_equivalente) > $contrato->monto_mensual) {
                $disponible = $contrato->monto_mensual - $yaPagado;
                throw new Exception("Exceso de Sueldo: El saldo disponible para el mes {$this->mes_correspondiente} es de {$disponible}. Intenta pagar {$this->debe_equivalente}.");
            }
        }

        // 3. Control de Primas (No pagar más del total de la prima pactada)
        if ($this->prima_id) {
            $prima = Prima::find($this->prima_id);
            if (($prima->pagos()->sum('debe_equivalente') + $this->debe_equivalente) > $prima->monto_total) {
                throw new Exception("Exceso de Prima: El monto total de la prima es {$prima->monto_total} y se está superando con este pago.");
            }
        }
    }

    // --- Relaciones ---

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function prima(): BelongsTo
    {
        return $this->belongsTo(Prima::class);
    }
    public function cuentaFinanciera()
    {
        return $this->belongsTo(CuentaFinanciera::class, 'cuenta_financiera_id');
    }
}