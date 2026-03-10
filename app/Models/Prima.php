<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prima extends Model
{
    use HasUuids;

    protected $fillable = [
        'contrato_id',
        'descripcion',
        'monto_total',
        'fecha_pactada'
    ];

    protected $casts = [
        'fecha_pactada' => 'date',
        'monto_total'   => 'decimal:2'
    ];

    /**
     * Relación: Una prima pertenece a un contrato específico.
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    /**
     * Relación: Una prima puede tener múltiples pagos o anticipos asociados.
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    /**
     * Atributo Dinámico: Calcula el saldo pendiente de esta prima específica.
     * Resta los pagos realizados (en moneda equivalente del contrato).
     */
    public function getSaldoAttribute(): float
    {
        // Sumamos los pagos cuyo 'tipo_pago' sea 'Prima' o 'Anticipo Prima' vinculados a este ID
        $pagado = $this->pagos()->sum('monto_equivalente_contrato');
        
        return (float) ($this->monto_total - $pagado);
    }

    /**
     * Atributo Dinámico: Indica si la prima ya fue pagada en su totalidad.
     */
    public function getEsPagadaAttribute(): bool
    {
        return $this->saldo <= 0;
    }
}