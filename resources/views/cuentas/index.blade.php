@extends('adminlte::page')

@section('title', 'Cuentas de Personal')

@section('content_header')
    <h1 class="text-truncate">Cuentas Financieras</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-2 p-md-3">
            {{-- Buscador y Botones Responsivos --}}
            <div class="row mb-3">
                <div class="col-12 col-md-7 col-lg-8 mb-2 mb-md-0">
                    <form method="GET" id="searchForm">
                        <div class="input-group">
                            <input type="text" name="busqueda" value="{{ request('busqueda') }}" class="form-control" placeholder="Buscar titular, banco...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-md-5 col-lg-4 text-md-right">
                    <div class="btn-group w-100 w-md-auto">
                        <button class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#modalBanco">
                            <i class="fas fa-university"></i> <span class="d-none d-sm-inline">+ Banco</span><span class="d-inline d-sm-none">+Banco</span>
                        </button>
                        <button class="btn btn-success btn-sm" onclick="nuevaCuenta()">
                            <i class="fas fa-plus-circle"></i> Nueva Cuenta
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabla Responsiva --}}
            <div class="table-responsive">
                <table class="table table-sm table-hover table-striped table-bordered mb-0" style="min-width: 650px;">
                    <thead class="thead-dark">
                        <tr>
                            <th>Titular</th>
                            <th>Banco / Tipo</th>
                            <th>Identificador de Cuenta</th>
                            <th>Observaciones</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cuentas as $c)
                        <tr>
                            <td class="align-middle"><strong>{{ $c->persona->nombre }}</strong></td>
                            <td class="align-middle">
                                {{ $c->banco->nombre }} 
                                <span class="badge {{ $c->banco->tipo == 'tradicional' ? 'badge-info' : 'badge-warning' }}">
                                    {{ ucfirst($c->banco->tipo) }}
                                </span>
                            </td>
                            <td class="align-middle"><code>{{ $c->identificador_cuenta }}</code></td>
                            <td class="align-middle"><small class="text-muted">{{ Str::limit($c->observacion_cuenta, 30) ?? '-' }}</small></td>
                            <td class="text-center align-middle">
                                <button class="btn btn-xs btn-warning" 
                                    onclick="editarCuenta('{{ $c->id }}', '{{ $c->persona_id }}', '{{ $c->banco_id }}', '{{ $c->identificador_cuenta }}', '{{ $c->observacion_cuenta }}')"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">No se encontraron registros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                <small class="mb-2 mb-md-0 text-muted">Mostrando {{ $cuentas->firstItem() ?? 0 }} a {{ $cuentas->lastItem() ?? 0 }} de {{ $cuentas->total() }}</small>
                <div class="overflow-auto">
                    {{ $cuentas->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL BANCO --}}
    <div class="modal fade" id="modalBanco" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark">
                    <h5 class="modal-title">Nuevo Banco</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre del Banco</label>
                        <input type="text" id="nombre_banco" class="form-control" placeholder="Ej. BNB o Binance">
                    </div>
                    <div class="form-group mb-0">
                        <label>Tipo</label>
                        <select id="tipo_banco" class="form-control">
                            <option value="tradicional">Tradicional</option>
                            <option value="cripto">Cripto</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-block" id="btnGuardarBanco">Guardar Banco</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CUENTA --}}
    <div class="modal fade" id="modalCuenta" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('cuentas.store') }}" method="POST" id="formCuenta" class="modal-content">
                @csrf
                <div id="method_field"></div>
                <div class="modal-header bg-success text-white" id="modalHeader">
                    <h5 class="modal-title" id="modalTitle">Nueva Cuenta</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Titular (Persona)</label>
                        <select name="persona_id" id="select_persona" class="form-control select2" style="width:100%" required>
                            <option value="">-- Buscar Persona --</option>
                            @foreach($personas as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }} ({{ $p->ci }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Banco</label>
                        <select name="banco_id" id="select_banco" class="form-control select2" style="width:100%" required>
                            <option value="">-- Seleccione Banco --</option>
                            @foreach($bancos as $b)
                                <option value="{{ $b->id }}">{{ $b->nombre }} ({{ $b->tipo }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nro. Cuenta / Wallet</label>
                        <input type="text" name="identificador_cuenta" id="input_identificador" class="form-control" required placeholder="Ingrese identificador">
                    </div>
                    <div class="form-group mb-0">
                        <label>Observaciones</label>
                        <textarea name="observacion_cuenta" id="input_observacion" class="form-control" rows="2" placeholder="Opcional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success" id="btnSubmit">Guardar Cuenta</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
<style>
    /* Ajustes para móviles */
    @media (max-width: 576px) {
        .btn-group { flex-direction: column; }
        .btn-group .btn { width: 100%; border-radius: 4px !important; margin-bottom: 5px; }
        .card-body { padding: 10px; }
        h1 { font-size: 1.5rem; }
    }
    /* Estética de Select2 en AdminLTE */
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
    }
    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #adb5bd #f8f9fa;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Inicialización Select2 responsivo
        $('.select2').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#modalCuenta'),
            width: '100%'
        });

        // Guardar Banco vía AJAX
        $('#btnGuardarBanco').on('click', function() {
            let nombre = $('#nombre_banco').val();
            let tipo = $('#tipo_banco').val();

            if(!nombre) { alert('Nombre requerido'); return; }

            $.post("{{ route('bancos.ajax.store') }}", { 
                _token: "{{ csrf_token() }}",
                nombre: nombre,
                tipo: tipo 
            }, function(data) {
                let newOption = new Option(data.nombre + ' (' + data.tipo + ')', data.id, true, true);
                $('#select_banco').append(newOption).trigger('change');
                $('#modalBanco').modal('hide');
                $('#nombre_banco').val('');
                alert('Banco agregado correctamente');
            }).fail(function() {
                alert('Error: El banco ya existe o hubo un problema.');
            });
        });
    });

    function nuevaCuenta() {
        $('#formCuenta')[0].reset();
        $('#formCuenta').attr('action', "{{ route('cuentas.store') }}");
        $('#method_field').empty();
        $('#modalTitle').text('Nueva Cuenta');
        $('#modalHeader').removeClass('bg-warning').addClass('bg-success');
        $('#btnSubmit').removeClass('btn-warning').addClass('btn-success').text('Guardar Cuenta');
        $('.select2').val('').trigger('change');
        $('#modalCuenta').modal('show');
    }

    function editarCuenta(id, persona_id, banco_id, identificador, observacion) {
        $('#formCuenta').attr('action', '/cuentas/' + id);
        $('#method_field').html('@method("PUT")');
        $('#modalTitle').text('Editar Cuenta');
        $('#modalHeader').removeClass('bg-success').addClass('bg-warning');
        $('#btnSubmit').removeClass('btn-success').addClass('btn-warning').text('Actualizar Cambios');

        $('#select_persona').val(persona_id).trigger('change');
        $('#select_banco').val(banco_id).trigger('change');
        $('#input_identificador').val(identificador);
        $('#input_observacion').val(observacion);
        $('#modalCuenta').modal('show');
    }
</script>
@stop