<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL; // Importante añadir esta línea

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar HTTPS en producción (Railway)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Esto permite que 'can' => 'Administrador' funcione 
        Gate::before(function ($user, $ability) {
            return $user->hasRole($ability) ? true : null;
        });

        Paginator::useBootstrapFour();
        
        \Illuminate\Support\Carbon::setLocale('es');
        setlocale(LC_TIME, 'es_ES.UTF-8');
    }
}