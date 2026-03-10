@extends('adminlte::page')

@section('title', 'Gestión de Personal')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="text-dark font-weight-bold"><i class="fas fa-users-cog mr-2"></i>Gestión de Personas y Cargos</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-0">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item active">Personal</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    {{-- Notificaciones con estilo --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación Exitosa!',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false
                });
            });
        </script>
    @endif

    <div class="card card-outline card-purple shadow-sm">
        <div class="card-body p-3">
            {{-- Barra de Herramientas: Búsqueda y Acciones --}}
            <div class="row mb-4">
                <div class="col-12 col-md-6 col-lg-7 mb-2 mb-md-0">
                    <form method="GET" id="searchForm" class="form-inline">
                        <div class="input-group w-100">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-filter text-muted"></i></span>
                            </div>
                            <input type="text" name="busqueda" value="{{ request('busqueda') }}" 
                                   class="form-control" placeholder="Buscar por nombre o CI...">
                            
                            <select name="per_page" class="form-control" style="max-width: 75px;" onchange="this.form.submit()">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            </select>
                            
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-md-6 col-lg-5 text-md-right">
                    <div class="btn-group shadow-sm">
                        <button type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#modalCargo">
                            <i class="fas fa-briefcase"></i> + Cargo
                        </button>
                        <button type="button" class="btn btn-success" onclick="nuevaPersona()">
                            <i class="fas fa-user-plus"></i> Nueva Persona
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabla de Datos --}}
            <div class="table-responsive">
                <table class="table table-hover table-striped border-bottom mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>Personal</th>
                            <th>Identificación</th>
                            <th>Cargo / Posición</th>
                            <th>Contacto</th>
                            <th class="text-center">Origen</th>
                            <th class="text-center" style="width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($personas as $p)
                        <tr>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <div class="bg-purple text-white rounded-circle mr-2 d-flex justify-content-center align-items-center" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                        {{ substr($p->nombre, 0, 1) }}
                                    </div>
                                    <strong>{{ $p->nombre }}</strong>
                                </div>
                            </td>
                            <td class="align-middle text-muted">{{ $p->ci }}</td>
                            <td class="align-middle">
                                <span class="badge badge-info px-2 py-1">{{ $p->cargo->nombre ?? 'S/C' }}</span>
                                @if($p->posicion)<br><small class="text-secondary font-italic">{{ $p->posicion }}</small>@endif
                            </td>
                            <td class="align-middle"><i class="fas fa-phone-alt mr-1 text-xs"></i> {{ $p->telefono ?? '-' }}</td>
                            <td class="align-middle text-center">
                                <span class="badge {{ $p->nacionalidad == 'NACIONAL' ? 'badge-success' : 'badge-warning' }} badge-pill">
                                    {{ $p->nacionalidad }}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-warning font-weight-bold" 
                                            onclick="editarPersona('{{ $p->id }}', '{{ $p->nombre }}', '{{ $p->ci }}', '{{ $p->telefono }}', '{{ $p->cargo_id }}', '{{ $p->posicion }}', '{{ $p->nacionalidad }}')"
                                            data-toggle="tooltip" title="Editar información">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    
                                    {{-- Solo mostramos el botón de pagos si la persona tiene al menos un contrato --}}
                                    @if($p->contratos->count() > 0)
                                        <a href="{{ route('pagos.show', $p->id) }}" class="btn btn-sm btn-info font-weight-bold" 
                                        data-toggle="tooltip" title="Ver historial de pagos">
                                            <i class="fas fa-file-invoice-dollar"></i> Detalle Pagos
                                        </a>
                                    @else
                                        {{-- Opcional: Un botón deshabilitado o un mensaje que indique por qué no hay pagos --}}
                                        <button class="btn btn-sm btn-secondary font-weight-bold disabled" 
                                                data-toggle="tooltip" title="Sin contrato asignado">
                                            <i class="fas fa-times-circle"></i> Sin Contrato
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                                No se encontraron resultados para la búsqueda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Paginación --}}
            <div class="d-flex justify-content-between align-items-center mt-4">
                <p class="text-muted small">
                    Mostrando {{ $personas->firstItem() }} a {{ $personas->lastItem() }} de {{ $personas->total() }} registros
                </p>
                <div>
                    {{ $personas->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CARGO --}}
    <div class="modal fade" id="modalCargo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title font-weight-bold">Nuevo Cargo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="text-xs text-uppercase font-weight-bold">Nombre del Cargo</label>
                        <input type="text" id="nombre_cargo" class="form-control" placeholder="Ej. Entrenador">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-muted" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary shadow-sm" id="btnGuardarCargo">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PERSONA --}}
    <div class="modal fade" id="modalPersona" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('personas.store') }}" method="POST" id="formPersona" class="modal-content border-0 shadow-lg">
                @csrf
                <div id="method_field"></div>
                
                <div class="modal-header bg-success text-white" id="modalHeaderPersona">
                    <h5 class="modal-title font-weight-bold" id="modalTitlePersona">Registrar Persona</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Nombre Completo</label>
                        <input type="text" name="nombre" id="input_nombre" class="form-control border-success" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold">CI / Documento</label>
                                <input type="text" name="ci" id="input_ci" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Teléfono</label>
                                <input type="text" name="telefono" id="input_telefono" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Posición Técnica</label>
                                <input type="text" name="posicion" id="input_posicion" class="form-control" placeholder="Ej. Delantero, DT...">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Nacionalidad</label>
                                <select name="nacionalidad" id="input_nacionalidad" class="form-control select2" style="width: 100%">
                                    <option value="NACIONAL">NACIONAL</option>
                                    <option value="EXTRANJERO">EXTRANJERO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-primary">Asignar Cargo</label>
                        <select name="cargo_id" id="select_cargo" class="form-control select2" style="width: 100%">
                            <option value="">-- Seleccione un cargo --</option>
                            @foreach($cargos as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success shadow-sm" id="btnSubmitPersona">
                        <i class="fas fa-save mr-1"></i> Guardar Persona
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-purple { border-top: 3px solid #6f42c1; }
    .bg-purple { background-color: #6f42c1; }
    .table thead th { border-top: none; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; }
    .btn-group .btn { margin-right: 5px; border-radius: 4px !important; }
    .select2-container--bootstrap4 .select2-selection--single { height: calc(2.25rem + 2px) !important; }
    .badge-pill { padding-right: 0.6em; padding-left: 0.6em; }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#modalPersona')
        });

        // Configuración CSRF
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // AJAX Guardar Cargo
        $('#btnGuardarCargo').on('click', function() {
            let nombre = $('#nombre_cargo').val();
            if (!nombre) return Swal.fire('Error', 'Escriba el nombre del cargo', 'error');

            $.ajax({
                url: "{{ route('cargos.ajax.store') }}",
                type: "POST",
                data: { nombre: nombre },
                success: function(data) {
                    let newOption = new Option(data.nombre, data.id, true, true);
                    $('#select_cargo').append(newOption).trigger('change');
                    $('#modalCargo').modal('hide');
                    $('#nombre_cargo').val('');
                    Swal.fire('¡Éxito!', 'El cargo se registró correctamente', 'success');
                }
            });
        });
    });

    function nuevaPersona() {
        $('#formPersona').attr('action', "{{ route('personas.store') }}");
        $('#method_field').empty(); 
        $('#modalTitlePersona').text('Registrar Persona');
        $('#modalHeaderPersona').removeClass('bg-warning').addClass('bg-success');
        $('#btnSubmitPersona').removeClass('btn-warning').addClass('btn-success').html('<i class="fas fa-save mr-1"></i> Guardar Persona');
        
        $('#formPersona')[0].reset();
        $('#select_cargo').val('').trigger('change');
        $('#input_nacionalidad').val('NACIONAL').trigger('change');
        $('#modalPersona').modal('show');
    }

    function editarPersona(id, nombre, ci, telefono, cargo_id, posicion, nacionalidad) {
        $('#formPersona').attr('action', '/personas/' + id);
        $('#method_field').html('@method("PUT")');
        
        $('#modalTitlePersona').text('Editar Información');
        $('#modalHeaderPersona').removeClass('bg-success').addClass('bg-warning');
        $('#btnSubmitPersona').removeClass('btn-success').addClass('btn-warning').html('<i class="fas fa-sync mr-1"></i> Actualizar Cambios');

        $('#input_nombre').val(nombre);
        $('#input_ci').val(ci);
        $('#input_telefono').val(telefono);
        $('#input_posicion').val(posicion);
        
        $('#select_cargo').val(cargo_id).trigger('change');
        $('#input_nacionalidad').val(nacionalidad).trigger('change');

        $('#modalPersona').modal('show');
    }
</script>
@stop