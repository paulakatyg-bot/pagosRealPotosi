@extends('adminlte::page')

@section('title', 'Gestión de Pagos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-wallet text-success mr-2"></i> Control de Pagos y Salarios</h1>
        <button class="btn btn-success shadow" data-toggle="modal" data-target="#modalPago">
            <i class="fas fa-plus-circle"></i> Nuevo Registro
        </button>
    </div>
@stop

@section('content')
    {{-- Notificaciones --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="icon fas fa-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="icon fas fa-ban"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Tabla de Registros --}}
    <div class="card card-outline card-success shadow">
        <div class="card-header border-0">
            <h3 class="card-title text-bold">Libro de Transacciones</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted text-uppercase" style="font-size: 0.85rem;">
                        <tr>
                            <th>Beneficiario / Fecha</th>
                            <th>Referencia / Cuenta Destino</th>
                            <th>Tipo / Mes</th>
                            <th>Monto Salida</th>
                            <th class="text-right">Equiv. Contrato</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pagos as $pago)
                        <tr>
                            <td>
                                <span class="text-bold d-block">{{ $pago->persona->nombre }}</span>
                                <small class="text-muted"><i class="far fa-calendar-alt"></i> {{ $pago->fecha_operacion->format('d/m/Y') }}</small>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $pago->comprobante_fise ?? 'N/A' }}</span>
                                <br>
                                @if($pago->cuentaFinanciera)
                                    <small class="text-success font-weight-bold">
                                        <i class="fas fa-university"></i> {{ $pago->cuentaFinanciera->banco->nombre }} 
                                        ({{ substr($pago->cuentaFinanciera->identificador_cuenta, -4) }})
                                    </small>
                                @else
                                    <small class="text-muted"><i class="fas fa-hand-holding-usd"></i> Efectivo / Caja</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ str_contains($pago->tipo_pago, 'Anticipo') ? 'badge-warning' : 'badge-info' }}">
                                    {{ $pago->tipo_pago }}
                                </span>
                                <br><small class="text-muted">{{ $pago->mes_correspondiente ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="text-bold">{{ $pago->moneda_pago }} {{ number_format($pago->monto_pagado, 2) }}</span>
                                <br><small class="text-xs text-muted">T/C: {{ number_format($pago->tipo_cambio, 2) }}</small>
                            </td>
                            
                            <td class="text-right">
                                <div class="btn-group">
                                    <span class="text-primary text-bold mr-2" style="font-size: 1.1rem;">
                                        {{ $pago->contrato->moneda }} {{ number_format($pago->debe_equivalente, 2) }}
                                    </span>
                                    <a href="{{ route('pagos.pdf', $pago->id) }}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">No se registran pagos en el sistema.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-0">
            {{ $pagos->links() }}
        </div>
    </div>

    {{-- MODAL INTELIGENTE --}}
    <div class="modal fade" id="modalPago" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('pagos.store') }}" method="POST" id="formPago">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-cash-register mr-2"></i> Registro de Pago</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label><i class="fas fa-user"></i> 1. Trabajador</label>
                                <select name="persona_id" id="persona_id" class="form-control select2" style="width: 100%" required>
                                    <option value="">Seleccione trabajador...</option>
                                    @foreach($personas as $persona)
                                        <option value="{{ $persona->id }}">{{ $persona->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label><i class="fas fa-credit-card"></i> 2. Cuenta Destino</label>
                                <select name="cuenta_financiera_id" id="cuenta_financiera_id" class="form-control select2" style="width: 100%">
                                    <option value="">-- Pago en Efectivo --</option>
                                    @foreach($cuentas as $cuenta)
                                        <option value="{{ $cuenta->id }}" data-persona="{{ $cuenta->persona_id }}">
                                            {{ $cuenta->banco->nombre }} - {{ $cuenta->identificador_cuenta }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- PANEL DE INFORMACIÓN DE SALDOS --}}
                        <div id="panel-info-pago" class="row d-none mb-3">
                            <div class="col-12">
                                <div class="callout callout-info bg-light shadow-sm border-left-info py-2">
                                    <div class="row text-center">
                                        <div class="col-sm-4 border-right">
                                            <label class="text-xs text-uppercase text-muted mb-0">Pactado</label>
                                            <h6 id="info-pactado" class="text-bold mb-0">-</h6>
                                        </div>
                                        <div class="col-sm-4 border-right">
                                            <label class="text-xs text-uppercase text-muted mb-0">Ya Pagado</label>
                                            <h6 id="info-pagado" class="text-danger text-bold mb-0">-</h6>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="text-xs text-uppercase text-muted mb-0">Disponible</label>
                                            <h6 id="info-saldo" class="text-success text-bold mb-0">-</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Categoría de Pago</label>
                                <select name="tipo_pago" id="tipo_pago" class="form-control" required>
                                    <option value="Sueldo">Sueldo Mensual</option>
                                    <option value="Anticipo Sueldo">Anticipo Sueldo</option>
                                    <option value="Prima">Pago Prima</option>
                                    <option value="Anticipo Prima">Anticipo Prima</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group" id="cont-mes">
                                <label>Mes Correspondiente</label>
                                <input type="month" name="mes_correspondiente" id="mes_correspondiente" class="form-control" value="{{ date('Y-m') }}">
                            </div>
                            <div class="col-md-12 form-group d-none" id="cont-prima">
                                <label class="text-primary font-weight-bold">Seleccionar Prima</label>
                                <select name="prima_id" id="prima_id" class="form-control border-primary select2" style="width: 100%"></select>
                            </div>
                        </div>

                        <div class="row bg-dark p-3 rounded shadow-inner mb-3 no-gutters">
                            <div class="col-md-3 px-1 border-right border-secondary text-center">
                                <label class="text-xs text-gray">Monto Pagado</label>
                                <input type="number" step="0.01" name="monto_pagado" id="monto_pagado" class="form-control text-bold border-0 bg-transparent text-white text-center" style="font-size: 1.2rem" required>
                                <div id="error-monto-msg" class="text-danger text-xs d-none"><i class="fas fa-times"></i> Saldo excedido</div>
                            </div>
                            <div class="col-md-3 px-1 border-right border-secondary text-center">
                                <label class="text-xs text-gray">Moneda Salida</label>
                                <select name="moneda_pago" id="moneda_pago" class="form-control border-0 bg-transparent text-white text-center">
                                    <option value="BS" class="text-dark">Bolivianos (BS)</option>
                                    <option value="USD" class="text-dark">Dólares (USD)</option>
                                </select>
                            </div>
                            <div class="col-md-3 px-1 border-right border-secondary text-center">
                                <label class="text-xs text-gray">Tipo de Cambio</label>
                                <input type="number" step="0.0001" name="tipo_cambio" id="tipo_cambio" class="form-control border-0 bg-transparent text-white text-center" value="6.96">
                            </div>
                            <div class="col-md-3 px-2 text-center">
                                <label class="text-xs text-gray">Equivalente</label>
                                <h4 class="text-success mb-0 text-bold mt-1" id="txt-equivalente">0.00</h4>
                                <small id="txt-moneda-con" class="text-xs text-muted">---</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <input type="text" name="comprobante_fise" class="form-control" placeholder="Ref. Comprobante">
                            </div>
                            <div class="col-md-6 form-group">
                                <input type="text" name="observacion" class="form-control" placeholder="Observaciones...">
                            </div>
                            <div class="col-md-12">
                                <label class="text-xs">Fecha de Operación:</label>
                                <input type="date" name="fecha_operacion" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-link text-muted" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success px-5 shadow text-bold" id="btn-submit">
                            <i class="fas fa-check-circle mr-1"></i> PROCESAR PAGO
                        </button>
                    </div>
                </div>
                <input type="hidden" name="contrato_id" id="contrato_id">
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    let contratoGlobal = null;
    let saldoDisponibleGlobal = 0;

    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4' });

        // Eventos principales
        $('#persona_id, #mes_correspondiente').on('change', function() {
            obtenerDatosYSaldo();
            filtrarCuentas();
        });

        $('#tipo_pago').on('change', function() {
            const isPrima = $(this).val().includes('Prima');
            $('#cont-prima').toggleClass('d-none', !isPrima);
            $('#cont-mes').toggleClass('d-none', isPrima);
            $('#panel-info-pago').addClass('d-none');
            
            if(!isPrima) {
                obtenerDatosYSaldo();
            } else {
                actualizarSaldoPrima();
            }
        });

        $('#prima_id').on('change', actualizarSaldoPrima);
        $('#monto_pagado, #tipo_cambio, #moneda_pago').on('input change', calc);

        function filtrarCuentas() {
            const pid = $('#persona_id').val();
            $('#cuenta_financiera_id option').each(function() {
                const optPid = $(this).data('persona');
                $(this).prop('disabled', pid && optPid != pid && $(this).val() != "");
            });
            $('#cuenta_financiera_id').val('').trigger('change');
        }

        function obtenerDatosYSaldo() {
            const pid = $('#persona_id').val();
            const mes = $('#mes_correspondiente').val();
            const tipo = $('#tipo_pago').val();

            if (!pid) return;

            $.get(`/pagos/datos-contrato/${pid}`, function(data) {
                contratoGlobal = data;
                $('#contrato_id').val(data.id);
                $('#txt-moneda-con').text(data.moneda);

                // Cargar Primas en el select
                let htmlP = '<option value="">-- Seleccionar Prima --</option>';
                data.primas.forEach(p => {
                    htmlP += `<option value="${p.id}" data-total="${p.monto_total}">${p.descripcion} (Saldo: ${p.monto_total})</option>`;
                });
                $('#prima_id').html(htmlP);

                // Si es sueldo, traer saldo del mes
                if(!tipo.includes('Prima') && mes) {
                    $.get(`/pagos/get-saldo-mes/${pid}/${mes}`, function(res) {
                        mostrarPanelSaldo(res.pactado, res.pagado, res.saldo, res.moneda);
                    });
                }
            }).fail(() => alert('Trabajador sin contrato activo.'));
        }

        function actualizarSaldoPrima() {
            const selected = $('#prima_id font:selected'); // Nota: Si usas select2 es mejor buscar por id
            const primaId = $('#prima_id').val();
            if(!primaId || !contratoGlobal) return;

            // Buscamos la prima en los datos del contrato que ya tenemos
            const prima = contratoGlobal.primas.find(p => p.id == primaId);
            if(prima) {
                // Aquí podrías necesitar un endpoint que te dé el saldo real de la prima 
                // o usar la data cargada en contratoGlobal si incluyes los pagos
                mostrarPanelSaldo(prima.monto_total, 0, prima.monto_total, contratoGlobal.moneda);
            }
        }

        function mostrarPanelSaldo(pactado, pagado, saldo, moneda) {
            saldoDisponibleGlobal = parseFloat(saldo);
            $('#panel-info-pago').removeClass('d-none');
            $('#info-pactado').text(`${moneda} ${pactado.toLocaleString()}`);
            $('#info-pagado').text(`${moneda} ${pagado.toLocaleString()}`);
            $('#info-saldo').text(`${moneda} ${saldo.toLocaleString()}`);
            calc();
        }

        function calc() {
            if(!contratoGlobal) return;
            let m = parseFloat($('#monto_pagado').val()) || 0;
            let tc = parseFloat($('#tipo_cambio').val()) || 1;
            let mp = $('#moneda_pago').val();
            let mc = contratoGlobal.moneda;
            
            let equiv = (mp === mc) ? m : (mp === 'BS' ? m / tc : m * tc);
            $('#txt-equivalente').text(equiv.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            // VALIDACIÓN VISUAL
            const btn = $('#btn-submit');
            const error = $('#error-monto-msg');
            
            if (equiv > (saldoDisponibleGlobal + 0.05)) {
                $('#txt-equivalente').removeClass('text-success').addClass('text-danger');
                error.removeClass('d-none');
                btn.prop('disabled', true).removeClass('btn-success').addClass('btn-danger').html('<i class="fas fa-ban"></i> EXCEDIDO');
            } else {
                $('#txt-equivalente').removeClass('text-danger').addClass('text-success');
                error.addClass('d-none');
                btn.prop('disabled', m <= 0).removeClass('btn-danger').addClass('btn-success').html('<i class="fas fa-check-circle"></i> PROCESAR PAGO');
            }
        }
    });
</script>
@stop