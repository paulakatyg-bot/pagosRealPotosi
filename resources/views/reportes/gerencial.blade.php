@extends('adminlte::page')

@section('title', 'Reporte Gerencial')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">
            <i class="fas fa-chart-line mr-2 text-primary"></i>Resumen Financiero: 
            <span class="text-capitalize text-primary">
                {{ \Carbon\Carbon::create()->month((int)$mes)->translatedFormat('F') }} {{ $anio }}
            </span>
        </h1>
        <div>
            {{-- BOTÓN PDF: Envía los mismos filtros de mes y año --}}
            <a href="{{ route('reportes.pdf', ['mes' => $mes, 'anio' => $anio]) }}" target="_blank" class="btn btn-danger btn-sm shadow-sm">
                <i class="fas fa-file-pdf mr-1"></i> Exportar PDF
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    
    {{-- FILTROS DE TIEMPO --}}
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-body py-2">
            <form action="{{ route('reportes.gerencial') }}" method="GET" class="row align-items-center">
                <div class="col-md-auto">
                    <span class="text-muted font-weight-bold text-uppercase small">Periodo de Consulta:</span>
                </div>
                <div class="col-md-3">
                    <select name="mes" class="form-control form-control-sm select2" onchange="this.form.submit()">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $mes == $i ? 'selected' : '' }}>
                                {{ ucfirst(\Carbon\Carbon::create()->month($i)->translatedFormat('F')) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="anio" class="form-control form-control-sm" onchange="this.form.submit()">
                        @for($a = now()->year; $a >= now()->year - 2; $a--)
                            <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endfor
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- INDICADORES CLAVE (KPIs) --}}
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-white border shadow-sm">
                <div class="inner text-dark">
                    <p class="text-uppercase mb-1 small" style="letter-spacing: 0.5px">Total Pactado (Periodo)</p>
                    <h3 class="font-weight-bold">{{ number_format($totalPactado, 2) }} <small>Bs.</small></h3>
                </div>
                <div class="icon text-primary"><i class="fas fa-file-contract"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-white border shadow-sm">
                <div class="inner text-dark">
                    <p class="text-uppercase mb-1 small" style="letter-spacing: 0.5px">Total Pagado (Efectivo)</p>
                    <h3 class="text-success font-weight-bold">{{ number_format($totalPagadoMes, 2) }} <small>Bs.</small></h3>
                </div>
                <div class="icon text-success"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-white border shadow-sm">
                <div class="inner text-dark">
                    <p class="text-uppercase mb-1 small" style="letter-spacing: 0.5px">Diferencia Pendiente</p>
                    <h3 class="text-danger font-weight-bold">{{ number_format($totalPactado - $totalPagadoMes, 2) }} <small>Bs.</small></h3>
                </div>
                <div class="icon text-danger"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>

    {{-- TABLA DETALLADA CON PAGINACIÓN --}}
    <div class="card card-dark shadow">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-list-ul mr-2"></i>Detalle de Planilla Mensual
            </h3>
            <div class="card-tools">
                <span class="badge badge-info p-2">Paginación: 5 registros</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-valign-middle mb-0">
                    <thead class="bg-light text-xs text-uppercase">
                        <tr>
                            <th class="pl-4 py-3">Personal / Cargo</th>
                            <th class="text-right">Sueldo Pactado</th>
                            <th class="text-right">Abonado (Bs.)</th>
                            <th class="text-right">Saldo Pendiente</th>
                            <th class="text-center" style="width: 20%">Progreso de Pago</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($reporteJugadores as $r)
                        @php 
                            // Manejo de datos si vienen como objeto o array
                            $sueldo = is_array($r) ? $r['sueldo'] : $r->sueldo;
                            $pagado = is_array($r) ? $r['pagado'] : $r->pagado;
                            $nombre = is_array($r) ? $r['nombre'] : $r->nombre;
                            $cargo = is_array($r) ? $r['cargo'] : $r->cargo;
                            $saldo = is_array($r) ? $r['saldo'] : $r->saldo;

                            $porcentaje = $sueldo > 0 ? ($pagado / $sueldo) * 100 : 0;
                            $porcentajeVisual = $porcentaje > 100 ? 100 : $porcentaje;
                            $colorProgreso = $porcentaje >= 100 ? 'bg-success' : ($porcentaje > 0 ? 'bg-warning' : 'bg-danger');
                        @endphp
                        <tr>
                            <td class="pl-4 py-3">
                                <span class="d-block font-weight-bold text-uppercase">{{ $nombre }}</span>
                                <span class="badge badge-light border text-muted small">{{ $cargo }}</span>
                            </td>
                            <td class="text-right font-weight-bold text-muted">
                                {{ number_format($sueldo, 2) }} <small>Bs.</small>
                            </td>
                            <td class="text-right text-success font-weight-bold">
                                {{ number_format($pagado, 2) }}
                            </td>
                            <td class="text-right text-danger font-weight-bold">
                                {{ number_format($saldo, 2) }}
                            </td>
                            <td class="text-center">
                                <div class="progress progress-xxs mb-1 shadow-sm">
                                    <div class="progress-bar {{ $colorProgreso }}" style="width: {{ $porcentajeVisual }}%"></div>
                                </div>
                                <span class="badge {{ $colorProgreso }} text-xs">{{ round($porcentaje) }}%</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-info-circle mr-2 fa-2x d-block mb-3"></i>
                                No se encontraron registros para el periodo seleccionado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- FOOTER CON PAGINADOR --}}
        <div class="card-footer bg-white border-top">
            <div class="row align-items-center">
                <div class="col-sm-6 text-muted small">
                    Mostrando del {{ $reporteJugadores->firstItem() }} al {{ $reporteJugadores->lastItem() }} 
                    de un total de {{ $reporteJugadores->total() }} personas.
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        {{ $reporteJugadores->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    @media print {
        .main-header, .main-sidebar, .btn, .card-outline, form, .card-footer, .badge-info { display: none !important; }
        .content-wrapper { margin-left: 0 !important; background: white !important; }
        .card { border: 1px solid #ddd !important; box-shadow: none !important; }
        .card-dark .card-header { background-color: #343a40 !important; color: white !important; }
    }
    
    .small-box .icon { top: 10px; right: 15px; font-size: 50px; opacity: 0.1; transition: 0.3s; }
    .progress-xxs { height: 6px; border-radius: 10px; background-color: #eee; }
    .table-valign-middle td { vertical-align: middle !important; }
    
    /* Estilo para que el paginador no se vea gigante */
    .pagination { margin: 0; }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({ theme: 'bootstrap4' });
        }
    });
</script>
@stop