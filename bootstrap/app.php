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
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // 🦁 LIBERAMOS LA RUTA DEL BOT DEL TOKEN CSRF
        $middleware->validateCsrfTokens(except: [
            'bot/consultar', 
        ]);

        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // Manejo de errores de Spatie (Tu código actual)
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            return response()->view('errors.403', [
                'exception' => $e,
                'message' => 'No tienes los roles necesarios para acceder a este módulo.'
            ], 403);
        });

        // Manejo general de errores HTTP
        $exceptions->render(function (HttpException $e, Request $request) {
            $status = $e->getStatusCode();
            $messages = [
                401 => 'Sesión expirada o no autorizada.',
                403 => 'Acceso restringido. No tienes permisos suficientes.',
                404 => 'La página que buscas no existe o fue movida.',
                500 => 'Error interno del servidor. Lo estamos revisando.',
            ];

            $message = $messages[$status] ?? $e->getMessage();

            if (view()->exists("errors.{$status}")) {
                return response()->view("errors.{$status}", ['exception' => $e, 'message' => $message], $status);
            }

            return response()->view("errors.403", ['exception' => $e, 'message' => $message], $status);
        });

    })->create();