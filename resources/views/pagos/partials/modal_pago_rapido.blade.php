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
                                @foreach($personas as $p)
                                    <option value="{{ $p->id }}" {{ (isset($persona) && $persona->id == $p->id) ? 'selected' : '' }}>
                                        {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label><i class="fas fa-credit-card"></i> 2. Cuenta Destino</label>
                            <select name="cuenta_financiera_id" id="cuenta_financiera_id" class="form-control select2" style="width: 100%">
                                <option value="">-- Pago en Efectivo --</option>
                                @foreach($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}" data-persona="{{ $cuenta->persona_id }}">
                                        {{ $cuenta->banco->nombre }} - {{ $cuenta->identificador_cuenta }} ({{ $cuenta->persona->nombre }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Panel de Información de Saldo --}}
                    <div id="panel-info-pago" class="row d-none mb-3">
                        <div class="col-12">
                            <div class="callout callout-info bg-light shadow-sm border-left-info">
                                <div class="row text-center">
                                    <div class="col-sm-4 border-right text-xs">
                                        <label class="text-uppercase text-muted">Sueldo Pactado</label>
                                        <h5 id="info-pactado" class="text-bold mb-0">0.00</h5>
                                    </div>
                                    <div class="col-sm-4 border-right text-xs">
                                        <label class="text-uppercase text-muted">Ya Pagado</label>
                                        <h5 id="info-pagado" class="text-danger text-bold mb-0">0.00</h5>
                                    </div>
                                    <div class="col-sm-4 text-xs">
                                        <label class="text-uppercase text-muted">Saldo Pendiente</label>
                                        <h5 id="info-saldo" class="text-success text-bold mb-0" style="font-size: 1.3rem">0.00</h5>
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
                            <label class="text-primary font-weight-bold">Seleccionar Prima del Jugador</label>
                            <select name="prima_id" id="prima_id" class="form-control border-primary select2" style="width: 100%"></select>
                        </div>
                    </div>

                    <div class="row bg-dark p-3 rounded shadow-inner mb-3 no-gutters">
                        <div class="col-md-3 px-1 border-right border-secondary">
                            <label class="text-xs text-gray">Monto Pagado</label>
                            <input type="number" step="0.01" name="monto_pagado" id="monto_pagado" 
                                   class="form-control text-bold border-0 bg-transparent text-white" 
                                   style="font-size: 1.2rem" placeholder="0.00" required>
                            <div id="error-monto-excedido" class="text-danger text-xs mt-1 d-none" style="position: absolute;">
                                Excede el saldo
                            </div>
                        </div>
                        <div class="col-md-3 px-1 border-right border-secondary">
                            <label class="text-xs text-gray">Moneda Salida</label>
                            <select name="moneda_pago" id="moneda_pago" class="form-control border-0 bg-transparent text-white">
                                <option value="BS" class="text-dark">BS (Bolivianos)</option>
                                <option value="USD" class="text-dark">USD (Dólares)</option>
                            </select>
                        </div>
                        <div class="col-md-3 px-1 border-right border-secondary">
                            <label class="text-xs text-gray">T. Cambio</label>
                            <input type="number" step="0.0001" name="tipo_cambio" id="tipo_cambio" class="form-control border-0 bg-transparent text-white" value="6.96">
                        </div>
                        <div class="col-md-3 px-2 text-center">
                            <label class="text-xs text-gray">Equivalente Contrato</label>
                            <h4 class="text-success mb-0 text-bold mt-1" id="txt-equivalente">0.00</h4>
                            <small id="txt-moneda-con" class="text-xs text-muted">---</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <input type="text" name="comprobante_fise" class="form-control" placeholder="Referencia Comprobante / FISE">
                        </div>
                        <div class="col-md-6 form-group">
                            <input type="text" name="observacion" class="form-control" placeholder="Observaciones generales...">
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