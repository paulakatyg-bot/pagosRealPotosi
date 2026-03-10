@extends('adminlte::page')

@section('title', 'Gestión de Contratos')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-file-contract text-primary"></i> Contratos y Primas</h1>
            </div>
            <div class="col-sm-6 text-right">
                <button class="btn btn-success" data-toggle="modal" data-target="#modalContrato" onclick="prepareCreate()">
                    <i class="fas fa-plus"></i> Nuevo Contrato
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
    {{-- Alertas de Feedback --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="icon fas fa-ban"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <form action="{{ route('contratos.index') }}" method="GET">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por persona o modalidad..." value="{{ request('search') }}">
                    <span class="input-group-append">
                        <button type="submit" class="btn btn-info btn-flat">
                            <i class="fas fa-search"></i>
                        </button>
                    </span>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap">
                    <thead class="bg-light">
                        <tr>
                            <th>Persona</th>
                            <th>Sueldo Mensual</th>
                            <th>Primas</th>
                            <th>Vigencia</th>
                            <th width="120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contratos as $contrato)
                            <tr>
                                <td>
                                    <strong>{{ $contrato->persona->nombre }}</strong><br>
                                    <small class="badge badge-secondary">{{ $contrato->modalidad }}</small>
                                </td>
                                <td>
                                    <span class="text-bold text-primary">
                                        {{ $contrato->moneda }} {{ number_format($contrato->monto_mensual, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if($contrato->primas->count() > 0)
                                        <button class="btn btn-xs btn-outline-info" data-toggle="collapse" data-target="#primas-{{ $contrato->id }}">
                                            Ver {{ $contrato->primas->count() }} primas
                                        </button>
                                        <div id="primas-{{ $contrato->id }}" class="collapse mt-2">
                                            <ul class="list-unstyled small border-top pt-1">
                                                @foreach($contrato->primas as $prima)
                                                    <li class="mb-1 text-muted">
                                                        <i class="fas fa-arrow-right text-xs"></i> 
                                                        {{ $prima->descripcion }}: <strong>{{ number_format($prima->monto_total, 2) }}</strong>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-muted small">Sin primas</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt"></i> {{ $contrato->fecha_inicio ? $contrato->fecha_inicio->format('d/m/Y') : 'N/A' }}<br>
                                        <i class="fas fa-calendar-check text-danger"></i> {{ $contrato->fecha_fin ? $contrato->fecha_fin->format('d/m/Y') : 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info btn-edit" 
                                            data-id="{{ $contrato->id }}"
                                            data-persona="{{ $contrato->persona_id }}"
                                            data-monto="{{ $contrato->monto_mensual }}"
                                            data-moneda="{{ $contrato->moneda }}"
                                            data-inicio="{{ $contrato->fecha_inicio ? $contrato->fecha_inicio->format('Y-m-d') : '' }}"
                                            data-fin="{{ $contrato->fecha_fin ? $contrato->fecha_fin->format('Y-m-d') : '' }}"
                                            data-modalidad="{{ $contrato->modalidad }}"
                                            data-primas='@json($contrato->primas)'>
                                        <i class="fa fa-pen"></i>
                                    </button>

                                    <form action="{{ route('contratos.destroy', $contrato->id) }}" method="POST" style="display:inline" onsubmit="return confirm('¿Está seguro de eliminar este contrato?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No se encontraron registros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL HÍBRIDO --}}
    <div class="modal fade" id="modalContrato" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form id="formContrato" action="{{ route('contratos.store') }}" method="POST">
                @csrf
                <div id="method_field"></div> {{-- Se llena dinámicamente --}}
                
                <div class="modal-content">
                    <div id="modalHeader" class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalTitle">Registrar Nuevo Contrato</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Persona (Jugador/Staff)</label>
                                <select name="persona_id" class="form-control select2" style="width: 100%" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($personas as $persona)
                                        <option value="{{ $persona->id }}">{{ $persona->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Moneda</label>
                                <select name="moneda" class="form-control">
                                    <option value="BS">BS</option>
                                    <option value="USD">USD</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Sueldo Mensual</label>
                                <input type="number" step="0.01" name="monto_mensual" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Modalidad</label>
                                <input type="text" name="modalidad" class="form-control" placeholder="Ej: Profesional" required>
                            </div>
                        </div>

                        <hr>
                        <h5 class="text-bold"><i class="fas fa-gift text-warning"></i> Primas Adicionales</h5>
                        <div id="wrapper-primas" class="p-2 border rounded bg-light">
                            {{-- Contenedor de primas --}}
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addPrima()">
                            <i class="fas fa-plus"></i> Añadir Prima
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnSubmit">Guardar Contrato</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    let primaIndex = 0;

    // Reinicia el modal a estado "Crear"
    function prepareCreate() {
        $('#formContrato').attr('action', "{{ route('contratos.store') }}");
        $('#method_field').empty();
        $('#modalTitle').text('Registrar Nuevo Contrato');
        $('#modalHeader').removeClass('bg-info').addClass('bg-success');
        $('#btnSubmit').removeClass('btn-info').addClass('btn-success').text('Guardar Contrato');
        $('#formContrato')[0].reset();
        $('select[name="persona_id"]').val('').trigger('change');
        $('#wrapper-primas').empty();
        primaIndex = 0;
    }

    // Agrega una fila de prima
    function addPrima(descripcion = '', monto = '', fecha = '') {
        const wrapper = document.getElementById('wrapper-primas');
        const html = `
            <div class="row mb-2 pb-2 border-bottom prima-row" id="prima-row-${primaIndex}">
                <div class="col-md-5">
                    <input type="text" name="primas[${primaIndex}][descripcion]" value="${descripcion}" class="form-control form-control-sm" placeholder="Descripción" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="primas[${primaIndex}][monto_total]" value="${monto}" class="form-control form-control-sm" placeholder="Monto" required>
                </div>
                <div class="col-md-3">
                    <input type="date" name="primas[${primaIndex}][fecha_pactada]" value="${fecha}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePrima(${primaIndex})"><i class="fas fa-times"></i></button>
                </div>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', html);
        primaIndex++;
    }

    function removePrima(index) {
        $(`#prima-row-${index}`).remove();
    }

    // Lógica para llenar el modal al Editar
    $(document).on('click', '.btn-edit', function() {
        const btn = $(this);
        const id = btn.data('id');
        const form = $('#formContrato');

        // Configuración visual del modal para edición
        form.attr('action', `/contratos/${id}`);
        $('#method_field').html('@method("PUT")');
        $('#modalTitle').text('Editar Contrato');
        $('#modalHeader').removeClass('bg-success').addClass('bg-info');
        $('#btnSubmit').removeClass('btn-success').addClass('btn-info').text('Actualizar Cambios');

        // Llenado de campos
        form.find('select[name="persona_id"]').val(btn.data('persona')).trigger('change');
        form.find('select[name="moneda"]').val(btn.data('moneda'));
        form.find('input[name="monto_mensual"]').val(btn.data('monto'));
        form.find('input[name="fecha_inicio"]').val(btn.data('inicio'));
        form.find('input[name="fecha_fin"]').val(btn.data('fin'));
        form.find('input[name="modalidad"]').val(btn.data('modalidad'));

        // Cargar Primas existentes
        $('#wrapper-primas').empty();
        const primas = btn.data('primas');
        if (primas && primas.length > 0) {
            primas.forEach(p => {
                let fecha = p.fecha_pactada ? p.fecha_pactada.split('T')[0] : '';
                addPrima(p.descripcion, p.monto_total, fecha);
            });
        }

        $('#modalContrato').modal('show');
    });

    // Inicializar Select2
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4' });
    });
</script>
@stop