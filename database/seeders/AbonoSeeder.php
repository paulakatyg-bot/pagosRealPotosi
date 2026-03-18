<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\AbonoService;

class AbonoSeeder extends Seeder
{
    public function run(): void
    {
        $cantidad = 2000; 
        $prefijo = 'RB';

        $this->command->info("Generando $cantidad abonos seguros...");

        // Solo llamamos al service, él se encarga de todo el proceso y el guardado
        AbonoService::generarLote($cantidad, $prefijo);

        $this->command->info("¡Éxito! $cantidad tarjetas listas en la base de datos.");
    }
}