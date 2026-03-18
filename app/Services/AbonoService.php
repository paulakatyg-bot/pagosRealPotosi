<?php

namespace App\Services;

use App\Models\TarjetaAbono;
use Illuminate\Support\Str;

class AbonoService
{
    public static function generarLote($cantidad, $prefijo = 'RP')
    {
        $insertar = [];
        $codigosEnEsteLote = []; // Para evitar duplicados dentro del mismo grupo
        $generados = 0;

        while ($generados < $cantidad) {
            $base = strtoupper(Str::random(4));
            $pre_codigo = $prefijo . "-" . $base;
            $checkDigit = self::calcularCheckDigit($pre_codigo);
            $codigoVisual = $pre_codigo . "-" . $checkDigit;

            // 1. Validamos que no esté repetido en lo que ya enviamos a la BD
            // 2. Validamos que no esté repetido en el grupo actual que estamos armando
            if (!TarjetaAbono::where('codigo_visual', $codigoVisual)->exists() && 
                !in_array($codigoVisual, $codigosEnEsteLote)) {
                
                $firma = substr(hash_hmac('sha256', $codigoVisual, config('app.key')), 0, 4);

                $insertar[] = [
                    'id' => Str::uuid()->toString(),
                    'codigo_visual' => $codigoVisual,
                    'firma_seguridad' => $firma,
                    'estado' => 'DISPONIBLE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $codigosEnEsteLote[] = $codigoVisual;
                $generados++;
            }
        }

        // Insertamos en bloques
        foreach (array_chunk($insertar, 500) as $chunk) {
            TarjetaAbono::insert($chunk);
        }
    }

    private static function calcularCheckDigit($cadena)
    {
        $sum = 0;
        foreach (str_split($cadena) as $char) {
            $sum += ord($char);
        }
        return $sum % 10;
    }
}