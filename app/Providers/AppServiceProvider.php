<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;

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
        // Esto permite que 'can' => 'Administrador' funcione 
        // comprobando si el usuario tiene el ROL con ese nombre.
        Gate::before(function ($user, $ability) {
            return $user->hasRole($ability) ? true : null;
        });
        Paginator::useBootstrapFour();
        \Illuminate\Support\Carbon::setLocale('es');
        setlocale(LC_TIME, 'es_ES.UTF-8');
    }
}
