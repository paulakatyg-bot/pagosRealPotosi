<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Spatie\Permission\Exceptions\UnauthorizedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // 1. Capturar error de Spatie (Sin Roles/Permisos) y convertirlo en 403 español
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            return response()->view('errors.403', [
                'exception' => $e,
                'message' => 'No tienes los roles necesarios para acceder a este módulo.'
            ], 403);
        });

        // 2. Manejo general de errores HTTP (404, 500, 402, etc.)
        $exceptions->render(function (HttpException $e, Request $request) {
            $status = $e->getStatusCode();
            
            // Mapeo de mensajes en español
            $messages = [
                401 => 'Sesión expirada o no autorizada.',
                402 => 'Se requiere realizar un pago para acceder.',
                403 => 'Acceso restringido. No tienes permisos suficientes.',
                404 => 'La página que buscas no existe o fue movida.',
                419 => 'La página ha expirado por inactividad, intenta recargar.',
                429 => 'Demasiadas solicitudes. Intenta más tarde.',
                500 => 'Error interno del servidor. Lo estamos revisando.',
                503 => 'Sistema en mantenimiento. Volveremos pronto.',
            ];

            $message = $messages[$status] ?? $e->getMessage();

            // Si la vista del error existe (ej: errors/404.blade.php), la carga
            if (view()->exists("errors.{$status}")) {
                return response()->view("errors.{$status}", ['exception' => $e, 'message' => $message], $status);
            }

            // Si no existe una vista específica, usamos una genérica de 403 para todos
            return response()->view("errors.403", ['exception' => $e, 'message' => $message], $status);
        });

    })->create();