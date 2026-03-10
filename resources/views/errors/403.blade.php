@extends('adminlte::page')

@section('title', 'Acceso Restringido')

@section('content_header')
    <h1>Módulo de Seguridad</h1>
@stop

@section('content')
<div class="error-page mt-5">
    {{-- El código de estado lo obtenemos de la excepción --}}
    <h2 class="headline text-danger"> {{ $exception->getStatusCode() }}</h2>

    <div class="error-content">
        <h3>
            <i class="fas fa-exclamation-triangle text-danger"></i> 
            ¡Acceso denegado!
        </h3>

        <p class="mt-3">
            {{-- Aquí se mostrará: "No tienes los roles necesarios para acceder a este módulo." --}}
            <strong>{{ $message }}</strong>
        </p>

        <p>
            Parece que tu usuario actual no cuenta con los privilegios suficientes para realizar esta acción. 
            Si crees que esto es un error, contacta al administrador del sistema.
        </p>

        <div class="mt-4">
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>
            <button onclick="window.history.back();" class="btn btn-outline-secondary ml-2">
                <i class="fas fa-arrow-left"></i> Regresar
            </button>
        </div>
    </div>
</div>
@stop