<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cargo;
use App\Models\Banco;
use App\Models\User;
use App\Models\Role;
use App\Models\Persona;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SistemaInicialSeeder extends Seeder
{
    public function run()
    {
        // 1. LIMPIAR CACHÉ DE SPATIE
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. CREAR CARGOS
        $cargoAdmin = Cargo::create(['id' => (string) Str::uuid(), 'nombre' => 'Administrativo']);
        $cargoContador = Cargo::create(['id' => (string) Str::uuid(), 'nombre' => 'Contabilidad']);
        $cargoGerente = Cargo::create(['id' => (string) Str::uuid(), 'nombre' => 'Gerencia']);
        $cargoJugador = Cargo::create(['id' => (string) Str::uuid(), 'nombre' => 'Jugador']);
        Cargo::create(['id' => (string) Str::uuid(), 'nombre' => 'Director Técnico']);

        // 3. CREAR BANCOS
        Banco::create(['id' => (string) Str::uuid(), 'nombre' => 'Banco Unión', 'tipo' => 'tradicional']);
        Banco::create(['id' => (string) Str::uuid(), 'nombre' => 'Banco Mercantil', 'tipo' => 'tradicional']);
        Banco::create(['id' => (string) Str::uuid(), 'nombre' => 'Binance (USDT)', 'tipo' => 'cripto']);

        // 4. CREAR ROLES
        $roleAdmin = Role::create(['id' => (string) Str::uuid(), 'name' => 'Administrador', 'guard_name' => 'web']);
        $roleContador = Role::create(['id' => (string) Str::uuid(), 'name' => 'Contador', 'guard_name' => 'web']);
        $roleGerencial = Role::create(['id' => (string) Str::uuid(), 'name' => 'Gerencial', 'guard_name' => 'web']);

        // 5. CREAR USUARIOS DE PRUEBA
        $userAdmin = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Admin Sistema',
            'email' => 'admin@sistema.com',
            'password' => Hash::make('admin123'),
            'cargo_id' => $cargoAdmin->id,
        ]);
        $userAdmin->assignRole($roleAdmin);

        $userContador = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Usuario Contador',
            'email' => 'contador@sistema.com',
            'password' => Hash::make('conta123'),
            'cargo_id' => $cargoContador->id,
        ]);
        $userContador->assignRole($roleContador);

        $userGerente = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Usuario Gerente',
            'email' => 'gerente@sistema.com',
            'password' => Hash::make('gerente123'),
            'cargo_id' => $cargoGerente->id,
        ]);
        $userGerente->assignRole($roleGerencial);

        // 6. INSERTAR LISTA DE PERSONAS (JUGADORES)
        $personas = [
            ['DAVID AKOLOGO', 'ARQUERO', '11265797', 'NACIONAL'],
            ['CHRISTIAN MARTINEZ', 'ARQUERO', '40448730', 'EXTRANJERO'],
            ['VICTOR GRIMALDES', 'ARQUERO', '15722170', 'NACIONAL'],
            ['EMERSON VELASQUEZ', 'DEFENSOR', '14995303', 'NACIONAL'],
            ['LUCAS REVUELTA', 'DEFENSOR', '13674882', 'NACIONAL'],
            ['KEVIN VILLEGAS', 'DEFENSOR', '1061439062', 'EXTRANJERO'],
            ['CARLOS CHORE', 'DEFENSOR', '4998365', 'NACIONAL'],
            ['JUAN PABLO RIOJA', 'DEFENSOR', '7844016', 'NACIONAL'],
            ['ANDERSON BRITO', 'LATERAL', '12488343', 'NACIONAL'],
            ['WILLIAM FERNANDEZ', 'LATERAL', '5708670', 'NACIONAL'],
            ['FRAN SUPAYABE', 'LATERAL', '9823507', 'NACIONAL'],
            ['GONZALO AÑASGO', 'LATERAL', '17081173', 'NACIONAL'],
            ['JUAN PABLO GOMEZ', 'V. CONTENCIÓN', '13865580', 'NACIONAL'],
            ['JAIRO THOMAS', 'V. MIXTO', '7340810', 'NACIONAL'],
            ['ERICK VARGAS', 'V. CONTENCIÓN', '15357512', 'NACIONAL'],
            ['LUCIANO ROMERO', 'V. MIXTO', '38.837.681', 'EXTRANJERO'],
            ['IMANOL CARDENAS', 'V. MIXTO', '7211438', 'NACIONAL'],
            ['ARIEL LINO', 'V. MIXTO', '14458262', 'NACIONAL'],
            ['IGOR SOARES', 'V. CREACIÓN', 'MG15478029', 'EXTRANJERO'],
            ['BORIS CONDORI', 'V. CREACIÓN', '7956472', 'NACIONAL'],
            ['DANIEL CUELLAR', 'EXTREMO', '12752027', 'NACIONAL'],
            ['MAXIMILIANO GOMEZ', 'EXTREMO', '11265856', 'NACIONAL'],
            ['JEFERSON RIVAS', 'DELANTERO', '1.152.708.530', 'EXTRANJERO'],
            ['VLADIMIR CASTELLON', 'DELANTERO', '6432389', 'NACIONAL'],
            ['ALEJANDRO CERVANTES', 'DELANTERO', '6390515', 'NACIONAL'],
            ['FABRICIO SUAREZ', 'DELANTERO', '12385828', 'NACIONAL'],
        ];

        foreach ($personas as $p) {
            Persona::create([
                'id' => (string) Str::uuid(),
                'nombre' => $p[0],
                'posicion' => $p[1],
                'ci' => $p[2],
                'nacionalidad' => $p[3],
                'cargo_id' => $cargoJugador->id,
                'telefono' => null, 
            ]);
        }

        $this->command->info('Seed completado: Cargos, Bancos, Roles, Usuarios y 26 Jugadores creados.');
    }
}