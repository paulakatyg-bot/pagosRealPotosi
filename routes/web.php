<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CuentaFinancieraController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ReporteGerencialController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Redirección inicial (Fuera de auth para que el invitado pueda ver el login)
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Rutas de Autenticación (Login, Logout, etc.)
Auth::routes();

// 3. Rutas Protegidas (Solo para usuarios logueados)
Route::middleware(['auth'])->group(function () {
    
    // Panel Principal
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // --- MÓDULO DE PERSONAL ---
    Route::middleware(['role:Administrador'])->group(function () {
        Route::resource('personas', PersonaController::class);
        Route::post('cargos-ajax', [PersonaController::class, 'storeCargo'])->name('cargos.ajax.store');
    });

    // --- MÓDULO DE FINANZAS / CUENTAS ---
    Route::middleware(['role:Administrador|Contador'])->group(function () {
        Route::resource('cuentas', CuentaFinancieraController::class);
        Route::post('bancos-ajax', [CuentaFinancieraController::class, 'storeBanco'])->name('bancos.ajax.store');
    });

    // --- MÓDULO DE CONTRATOS ---
    Route::middleware(['role:Administrador'])->group(function () {
        Route::resource('contratos', ContratoController::class);
    });

    // --- MÓDULO DE PAGOS (TESORERÍA) ---
    Route::middleware(['role:Administrador|Contador'])->group(function () {
        // Rutas principales de pagos
        Route::resource('pagos', PagoController::class);

        //Route::get('pagos/persona/{id}', [PagoController::class, 'showDetalle'])
        //->name('pagos.showDetalle');
        
        // Ruta técnica para cargar datos dinámicos en el modal de pagos
        Route::get('pagos/datos-contrato/{persona_id}', [PagoController::class, 'getDatosContrato'])
             ->name('pagos.datos-contrato');
        Route::get('pagos/get-saldo-mes/{persona_id}/{mes}', [PagoController::class, 'getSaldoMes'])
        ->name('pagos.get-saldo-mes');
        Route::get('pagos/{id}/pdf', [PagoController::class, 'generarPDF'])
        ->name('pagos.pdf');
    });
    Route::middleware(['role:Gerencial|Administrador'])->group(function () {
    
        // Vista principal del consolidado
        Route::get('reporte-gerencial', [ReporteGerencialController::class, 'index'])
            ->name('reportes.gerencial');

        // Detalle
        Route::get('reporte-gerencial/detalle', [ReporteGerencialController::class, 'detalle'])
            ->name('reportes.gerencial.detalle');
        Route::get('reporte-gerencial/pdf', [ReporteGerencialController::class, 'exportarPDF'])
            ->name('reportes.pdf');   
      
    });

});