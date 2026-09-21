<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;

Route::post('/bot/consultar', function() {
    dd("LA PETICION LLEGO A API.PHP");
});
// Sin middlewares, sin grupos, directo al controlador
Route::post('/bot/consultar', [BotController::class, 'procesarConsulta']);