@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('title', 'ClubPagos - Iniciar Sesión')

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <style>
        /* Fondo difuminado Lila a Blanco */
        

        /* Caja de Login Estilizada */
        .login-box .card {
            border-top: 5px solid #6f42c1;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(111,66,193,0.15) !important;
        }

        /* ESTILO DE TEXTO MEJORADO (Estilo Premium) */
        .brand-text {
            font-size: 1.6rem;
            letter-spacing: -1px;
            color: #2d3436;
            margin-top: 15px;
            margin-bottom: 0;
        }

        .brand-text b {
            color: #6f42c1; /* Lila institucional */
            font-weight: 800;
        }

        .brand-sub {
            text-transform: uppercase;
            letter-spacing: 4px; /* Espaciado elegante */
            font-size: 0.65rem;
            color: #a29bfe;
            font-weight: 700;
            display: block;
            margin-top: -2px;
            margin-bottom: 20px;
        }

        /* Botón ClubPagos Lila */
        .btn-purple {
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
            border: none;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-purple:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(111,66,193,0.4);
            color: white;
        }

        .input-group-text {
            color: #6f42c1;
        }
    </style>
@stop

@php
    $loginUrl = View::getSection('login_url') ?? config('adminlte.login_url', 'login');
    $passResetUrl = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset');

    if (config('adminlte.use_route_url', false)) {
        $loginUrl = $loginUrl ? route($loginUrl) : '';
        $passResetUrl = $passResetUrl ? route($passResetUrl) : '';
    } else {
        $loginUrl = $loginUrl ? url($loginUrl) : '';
        $passResetUrl = $passResetUrl ? url($passResetUrl) : '';
    }
@endphp

@section('auth_header')
    <div class="text-center">
        {{-- ELIMINADA LA SOMBRA AQUÍ (para logos JPG) --}}
        <img src="{{ asset('img/escudo.png') }}" alt="Real Potosí" style="height: 75px;">
        
        {{-- Texto ClubPagos Estilizado --}}
        <h2 class="brand-text">Club<b>Pagos</b></h2>
        <span class="brand-sub">Real Potosí</span>
    </div>
@stop

@section('auth_body')
    <form action="{{ $loginUrl }}" method="post">
        @csrf

        {{-- Correo Electrónico --}}
        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}" placeholder="Correo electrónico" autofocus>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>

            @error('email')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- Contraseña --}}
        <div class="input-group mb-4">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="Contraseña">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>

            @error('password')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- Botón Ingresar Moderno --}}
        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-block btn-purple py-2 shadow-sm">
                    <span class="fas fa-sign-in-alt mr-2"></span>
                    INGRESAR AL PANEL
                </button>
            </div>
        </div>
    </form>
@stop

@section('auth_footer')
    <div class="text-center mt-4">
        <p class="text-muted small mb-0">ClubPagos - Ceac</p>
        <hr style="width: 40%; margin: 15px auto;">
        <p class="font-weight-bold text-uppercase" style="color: #6f42c1; letter-spacing: 2px; font-size: 0.7rem;">
            Real Potosí &copy; 2026
        </p>
    </div>
@stop