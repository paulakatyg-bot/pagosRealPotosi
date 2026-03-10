@extends('adminlte::page')

@section('title', 'Extracto Contable - ' . $persona->nombre)

@section('content')
<div class="container-fluid pt-3">
    {{-- SECCIÓN 1: PERFIL Y CUENTAS --}}
    <div class="row">
        <div class="col-md-4">
            {{-- Tarjeta de Perfil --}}
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle border-primary" 
                             src="https://ui-avatars.com/api/?name={{ urlencode($persona->nombre) }}&background=007bff&color=fff" 
                             alt="User profile">
                    </div>
                    <h3 class="profile-username text-center font-weight-bold" style="font-size: 1.1rem;">{{ $persona->nombre }}</h3>
                    <p class="text-muted text-center mb-2 text-sm">{{ $persona->cargo->nombre ?? 'Sin Cargo' }}</p>
                    <ul class="list-group list-group-unbordered text-sm">
                        <li class="list-group-item border-0 text-center">
                            <b>Sueldo Mensual:</b> <br>
                            <span class="text-primary font-weight-bold" style="font-size: 1.2rem;">
                                {{ number_format($contratoActivo->monto_mensual, 2) }} {{ $contratoActivo->moneda }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Cuentas Bancarias --}}
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header py-2">
                    <h3 class="card-title text-xs font-weight-bold uppercase"><i class="fas fa-university mr-2"></i> CUENTAS DESTINO</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <tbody class="text-xs">
                                @forelse($persona->cuentasFinancieras as $cta)
                                <tr>
                                    <td class="pl-3 py-2">
                                        <span class="font-weight-bold d-block text-truncate" style="max-width: 150px;">{{ $cta->banco->nombre }}</span>
                                        <code class="text-pink">{{ $cta->identificador_cuenta }}</code>
                                    </td>
                                    <td class="text-right pr-3">
                                        <span class="badge {{ $cta->es_primaria ? 'badge-success' : 'badge-light border' }}">
                                            {{ $cta->moneda }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td class="text-center py-3 text-muted">Sin cuentas registradas</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2: DETALLE DE PAGOS --}}
        <div class="col-md-8">
            {{-- Resumen de Contrato --}}
            <div class="card shadow-sm border-left-info mb-3">
                <div class="card-body py-2">
                    <div class="row text-center">
                        <div class="col-4 border-right">
                            <small class="text-muted d-block text-xs">INICIO</small>
                            <span class="text-sm font-weight-bold">{{ $contratoActivo->fecha_inicio->format('d/m/Y') }}</span>
                        </div>
                        <div class="col-4 border-right">
                            <small class="text-muted d-block text-xs">FIN</small>
                            <span class="text-sm font-weight-bold text-truncate">{{ $contratoActivo->fecha_fin ? $contratoActivo->fecha_fin->format('d/m/Y') : 'Indef.' }}</span>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block text-xs">ESTADO</small>
                            <span class="badge badge-success text-xs">{{ $contratoActivo->estado }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- AUXILIAR DE SUELDOS --}}
            <div class="card card-dark shadow">
                <div class="card-header py-2 d-flex align-items-center">
                    <h3 class="card-title font-weight-bold text-xs"><i class="fas fa-list-ul mr-2"></i> AUXILIAR DE SUELDOS</h3>
                    <div class="card-tools ml-auto">
                        <span class="badge badge-light text-dark text-xs">Scroll para ver más ↓</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive-scroll"> 
                        <table class="table table-hover mb-0">
                            <thead class="text-xs text-uppercase">
                                <tr>
                                    <th>Mes / Detalle</th>
                                    <th class="text-right d-none d-sm-table-cell">Pactado</th>
                                    <th class="text-right">Pagado/Saldo</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @foreach($mesesDetalle as $m)
                                    <tr class="bg-light font-weight-bold border-top">
                                        <td class="text-primary py-2">
                                            <i class="fas fa-calendar-alt mr-1"></i> {{ $m['nombre'] }}
                                        </td>
                                        <td class="text-right text-muted small d-none d-sm-table-cell py-2">
                                            {{ number_format($m['pactado'], 2) }}
                                        </td>
                                        <td class="text-right py-2">
                                            {{ number_format($m['pactado'], 2) }}
                                        </td>
                                        <td class="text-center py-2">
                                            @if($m['saldo'] > 0)
                                                <button onclick="pagoRapido('{{ $m['periodo'] }}', '{{ $m['saldo'] }}', 'Sueldo')" class="btn btn-primary btn-xs shadow-sm">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            @else
                                                <i class="fas fa-check-circle text-success"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    
                                    @php $saldoIterado = $m['pactado']; @endphp
                                    @foreach($m['tickets'] as $pago)
                                        @php $saldoIterado -= $pago->debe_equivalente; @endphp
                                        <tr class="border-0">
                                            <td class="pl-4 py-1 text-xs border-0 text-muted">
                                                ↳ {{ $pago->fecha_operacion->format('d/m') }} <span class="d-none d-md-inline">- {{ $pago->tipo_pago }}</span>
                                            </td>
                                            <td class="text-right border-0 text-success text-xs d-none d-sm-table-cell py-1 font-weight-bold">
                                                +{{ number_format($pago->debe_equivalente, 2) }}
                                            </td>
                                            <td class="text-right border-0 text-muted text-xs py-1">
                                                {{ number_format($saldoIterado, 2) }}
                                            </td>
                                            <td class="text-center border-0 py-1">
                                                <a href="{{ route('pagos.pdf', $pago->id) }}" target="_blank" class="text-danger btn-reprint" title="Reimprimir">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr class="border-0 bg-white">
                                        <td colspan="2" class="text-right font-weight-bold py-1 d-none d-sm-table-cell text-xs text-uppercase">Saldo Pendiente:</td>
                                        <td class="text-right font-weight-bold py-1 {{ $m['saldo'] > 0 ? 'text-danger' : 'text-success' }}">
                                            <small class="mr-1">{{ $contratoActivo->moneda }}</small>{{ number_format($m['saldo'], 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- CONTROL DE PRIMAS --}}
            <div class="card card-info shadow">
                <div class="card-header py-2">
                    <h3 class="card-title font-weight-bold text-xs"><i class="fas fa-star mr-2"></i> CONTROL DE PRIMAS</h3>
                </div>
                <div class="card-body p-0 text-sm">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="text-xs">
                                <tr>
                                    <th class="pl-3">Descripción</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">Saldo</th>
                                    <th class="text-right pr-3">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($primasDetalle as $pd)
                                <tr>
                                    <td class="pl-3 font-weight-bold text-xs">{{ $pd['descripcion'] }}</td>
                                    <td class="text-right text-xs">{{ number_format($pd['monto_total'], 2) }}</td>
                                    <td class="text-right text-danger font-weight-bold text-xs">{{ number_format($pd['saldo'], 2) }}</td>
                                    <td class="text-right pr-3">
                                        <div class="btn-group">
                                            @if($pd['saldo'] > 0)
                                                <button onclick="pagoRapido('', '{{ $pd['saldo'] }}', 'Prima', '{{ $pd['objeto']->id }}')" class="btn btn-info btn-xs">
                                                    <i class="fas fa-bolt"></i> Pagar
                                                </button>
                                            @else
                                                <span class="badge badge-success text-xs mr-1"><i class="fas fa-check"></i> OK</span>
                                            @endif

                                            @php 
                                                $pagoP = \App\Models\Pago::where('persona_id', $persona->id)->where('prima_id', $pd['objeto']->id)->latest()->first(); 
                                            @endphp
                                            @if($pagoP)
                                                <a href="{{ route('pagos.pdf', $pagoP->id) }}" target="_blank" class="btn btn-outline-danger btn-xs" title="Ver último recibo">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('pagos.partials.modal_pago_rapido')

@stop

@section('css')
<style>
    .border-left-info { border-left: 5px solid #17a2b8 !important; }
    .profile-user-img { width: 65px; height: 65px; margin-bottom: 10px; border: 3px solid #007bff; }
    .text-pink { color: #e83e8c; }
    .btn-reprint { font-size: 0.9rem; transition: transform 0.2s; display: inline-block; padding: 0 5px; }
    .btn-reprint:hover { transform: scale(1.3); color: #bd2130; }
    
    .table-responsive-scroll { max-height: 500px; overflow-y: auto; position: relative; }
    .table-responsive-scroll thead th { position: sticky; top: 0; background-color: #f4f6f9 !important; z-index: 10; box-shadow: inset 0 -1px 0 #dee2e6; }
    .table-responsive-scroll::-webkit-scrollbar { width: 5px; }
    .table-responsive-scroll::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        if ($.fn.select2) { $('.select2').select2({ theme: 'bootstrap4' }); }

        $('#persona_id').on('change', function() {
            let pid = $(this).val();
            if(!pid) return;
            $.get(`/pagos/datos-contrato/${pid}`, function(data) {
                $('#contrato_id').val(data.id);
                let html = '<option value="">-- Seleccionar --</option>';
                if(data.primas) data.primas.forEach(p => html += `<option value="${p.id}">${p.descripcion}</option>`);
                $('#prima_id').html(html).trigger('change');
            });
            filtrarCuentas(pid);
            actualizarInfoSaldos();
        });

        $('#mes_correspondiente, #tipo_pago, #prima_id').on('change', actualizarInfoSaldos);
        $('#monto_pagado').on('input', validarMonto);
    });

    function actualizarInfoSaldos() {
        const pid = $('#persona_id').val();
        const mes = $('#mes_correspondiente').val();
        const tipo = $('#tipo_pago').val();
        const primaId = $('#prima_id').val();
        if (!pid) return;

        let url = "";
        if ((tipo === 'Sueldo' || tipo === 'Anticipo Sueldo') && mes) url = `/pagos/get-saldo-mes/${pid}/${mes}`;
        else if ((tipo === 'Prima' || tipo === 'Anticipo Prima') && primaId) url = `/pagos/get-saldo-prima/${primaId}`;

        if (url) {
            $.get(url, function(data) {
                $('#panel-info-pago').removeClass('d-none');
                $('#info-pactado').text(parseFloat(data.pactado).toFixed(2));
                $('#info-pagado').text(parseFloat(data.pagado).toFixed(2));
                $('#info-saldo').text(parseFloat(data.saldo).toFixed(2));
                $('#monto_pagado').attr('data-max', data.saldo);
                validarMonto();
            });
        } else { $('#panel-info-pago').addClass('d-none'); }
    }

    function validarMonto() {
        let monto = parseFloat($('#monto_pagado').val()) || 0;
        let max = parseFloat($('#monto_pagado').attr('data-max')) || 9999999;
        let btn = $('#btn-submit');
        if (monto > (max + 0.01)) {
            $('#monto_pagado').addClass('is-invalid');
            btn.prop('disabled', true);
        } else {
            $('#monto_pagado').removeClass('is-invalid');
            btn.prop('disabled', false);
        }
    }

    function filtrarCuentas(pid) {
        $('#cuenta_financiera_id option').each(function() {
            const optPid = $(this).data('persona');
            $(this).prop('disabled', pid && optPid && optPid != pid);
        });
        $('#cuenta_financiera_id').val('').trigger('change');
    }

    function pagoRapido(mes, saldo, tipo, primaId = '') {
        const form = $('#formPago')[0];
        form.reset();
        $('#monto_pagado').attr('data-max', saldo).val(saldo);
        $('#modalPago').modal('show');
        
        setTimeout(() => {
            $('#persona_id').val('{{ $persona->id }}').trigger('change');
            $('#tipo_pago').val(tipo).trigger('change');
            if(tipo === 'Sueldo') {
                $('#cont-mes').removeClass('d-none');
                $('#cont-prima').addClass('d-none');
                $('#mes_correspondiente').val(mes).trigger('change');
            } else {
                $('#cont-mes').addClass('d-none');
                $('#cont-prima').removeClass('d-none');
                setTimeout(() => { if(primaId) $('#prima_id').val(primaId).trigger('change'); }, 500);
            }
        }, 300);
    }
</script>
@stop