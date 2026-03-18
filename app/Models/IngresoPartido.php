<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngresoPartido extends Model
{
    use HasUuids;

    protected $table = 'ingresos_partidos';

    protected $fillable = [
        'tarjeta_id',
        'evento_id',
        'fecha_ingreso'
    ];

    /**
     * Relación: El ingreso pertenece a una tarjeta específica
     */
    public function tarjeta(): BelongsTo
    {
        return $this->belongsTo(TarjetaAbono::class, 'tarjeta_id');
    }
}