<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// No es necesario importar SistemaInicialSeeder si están en la misma carpeta

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SistemaInicialSeeder::class,
        ]);
    }
}