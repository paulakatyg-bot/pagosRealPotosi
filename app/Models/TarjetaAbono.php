<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Para manejar UUIDs automáticamente
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TarjetaAbono extends Model
{
    use HasUuids;

    protected $table = 'tarjetas_abonos';

    protected $fillable = [
        'codigo_visual',
        'firma_seguridad',
        'persona_id',
        'estado'
    ];

    /**
     * Relación: Una tarjeta pertenece a una persona (socio)
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Relación: Una tarjeta tiene muchos registros de ingresos
     */
    public function ingresos(): HasMany
    {
        return $this->hasMany(IngresoPartido::class, 'tarjeta_id');
    }
}