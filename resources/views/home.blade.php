@extends('adminlte::page')

@section('title', 'Inico Panel')

@section('content_header')
    <h1>
        <i class="fas fa-tachometer-alt mr-2 text-secondary"></i>Dashboard Financiero
        <small class="text-muted text-capitalize">| {{ now()->locale('es')->monthName }} {{ now()->year }}</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    {{-- RESUMEN DE INDICADORES (KPIs) --}}
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm border">
                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase small font-weight-bold">Plantel Vigente</span>
                    <span class="info-box-number text-xl">{{ $cantJugadores }} <small>Jugadores</small></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm border">
                <span class="info-box-icon bg-primary"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase small font-weight-bold">Presupuesto Mes</span>
                    <span class="info-box-number text-xl">{{ number_format($totalPlanillaMes, 2) }} <small>Bs.</small></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm border">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase small font-weight-bold">Total Abonado</span>
                    <span class="info-box-number text-xl">{{ number_format($totalPagadoMes, 2) }} <small>Bs.</small></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm border">
                <span class="info-box-icon bg-danger"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase small font-weight-bold">Pendiente Hoy</span>
                    <span class="info-box-number text-xl">{{ number_format($saldoPendiente, 2) }} <small>Bs.</small></span>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLA DE PENDIENTES CON PAGINACIÓN --}}
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card card-outline card-dark shadow">
                <div class="card-header border-0">
                    <h3 class="card-title text-bold">
                        <i class="fas fa-exclamation-circle text-warning mr-2"></i>
                        Pendientes de Pago: {{ now()->locale('es')->monthName }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-danger p-2">{{ $pendientesCobro->total() }} personas identificadas</span>
                    </div>
                </div>
                
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-valign-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4 py-3">Personal / Cargo</th>
                                <th>Sueldo Pactado</th>
                                <th>Saldo Pendiente</th>
                                <th class="text-center" style="width: 20%">Estado de Pago</th>
                                <th class="text-right pr-4">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendientesCobro as $contrato)
                                @php 
                                    $porcentaje = ($contrato->monto_mensual > 0) 
                                        ? (($contrato->monto_mensual - $contrato->saldo_mes) / $contrato->monto_mensual) * 100 
                                        : 0;
                                    // Limitar el porcentaje visual entre 0 y 100
                                    $porcentajeVisual = max(0, min(100, $porcentaje));
                                    $colorProgreso = $porcentaje >= 100 ? 'bg-success' : ($porcentaje > 50 ? 'bg-info' : 'bg-warning');
                                @endphp
                                <tr>
                                    <td class="pl-4">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3 bg-light rounded-circle p-2 border">
                                                <i class="fas fa-user text-secondary"></i>
                                            </div>
                                            <div>
                                                <span class="d-block font-weight-bold text-uppercase">{{ $contrato->persona->nombre }}</span>
                                                <small class="badge badge-light border">{{ $contrato->persona->cargo->nombre ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold text-muted">
                                        {{ number_format($contrato->monto_mensual, 2) }} <small>Bs.</small>
                                    </td>
                                    <td class="text-danger font-weight-bold">
                                        {{ number_format($contrato->saldo_mes, 2) }} <small>Bs.</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="progress progress-xxs mb-1 shadow-sm">
                                            <div class="progress-bar {{ $colorProgreso }}" style="width: {{ $porcentajeVisual }}%"></div>
                                        </div>
                                        <small class="font-weight-bold">{{ round($porcentaje) }}% cubierto</small>
                                    </td>
                                    <td class="text-right pr-4">
                                        <a href="{{ route('pagos.show', $contrato->persona->id) }}" class="btn btn-sm btn-primary shadow-sm">
                                            <i class="fas fa-hand-holding-usd mr-1"></i> Pagar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-check-double fa-3x d-block mb-3 text-success opacity-50"></i>
                                        <p class="mb-0 font-weight-bold">¡Todo al día!</p>
                                        <p class="text-sm">No hay sueldos pendientes para {{ now()->locale('es')->monthName }}.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- FOOTER CON PAGINACIÓN --}}
                <div class="card-footer clearfix bg-white border-top">
                    <div class="row align-items-center">
                        <div class="col-sm-6 text-muted small">
                            Mostrando registros del {{ $pendientesCobro->firstItem() ?? 0 }} al {{ $pendientesCobro->lastItem() ?? 0 }} 
                            (Total: {{ $pendientesCobro->total() }})
                        </div>
                        <div class="col-sm-6">
                            <div class="float-right">
                                {{ $pendientesCobro->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Efecto Hover en las cajas de indicadores */
    .info-box { transition: all .3s ease; border-radius: 8px; }
    .info-box:hover { transform: translateY(-4px); box-shadow: 0 5px 15px rgba(0,0,0,.1) !important; }
    
    /* Progress bar ultra fina */
    .progress-xxs { height: 5px; border-radius: 10px; background-color: #f1f1f1; }
    
    /* Alineación de tabla */
    .table-valign-middle td { vertical-align: middle !important; }
    
    /* Ajuste para paginador en AdminLTE */
    .pagination { margin-bottom: 0; }
    .page-item.active .page-link { background-color: #343a40; border-color: #343a40; }
</style>
@stop

@section('js')
<script>
    console.log('Dashboard cargado correctamente.');
</script>
@stop