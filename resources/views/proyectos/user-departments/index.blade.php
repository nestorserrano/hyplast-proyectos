@extends('adminlte::page')

@section('title', 'Gestión Usuario-Departamentos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Asignación de Usuarios a Departamentos</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAssignment">
            <i class="fas fa-plus"></i> Nueva Asignación
        </button>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="assignments-table" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Departamento</th>
                        <th>Asignado Por</th>
                        <th width="100">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Modal Asignación -->
    <div class="modal fade" id="modalAssignment">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nueva Asignación</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="form-assignment">
                        <div class="form-group">
                            <label>Usuario</label>
                            <select name="user_id" id="user_id" class="form-control" required></select>
                        </div>
                        <div class="form-group">
                            <label>Departamento</label>
                            <select name="departamento_codigo" id="departamento_codigo" class="form-control" required></select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btn-save-assignment">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2-bootstrap4.min.css" rel="stylesheet" />
@stop

@section('js')
@include('scripts.datatables.datatables-user-departments')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar Select2 vacíos
            $('#user_id').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modalAssignment'),
                placeholder: 'Seleccione un usuario',
                allowClear: true
            });

            $('#departamento_codigo').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modalAssignment'),
                placeholder: 'Seleccione un departamento',
                allowClear: true
            });

            // Cargar datos cuando se abre el modal
            $('#modalAssignment').on('show.bs.modal', function() {
                // Cargar usuarios
                $.ajax({
                    url: '{{ route('proyectos.user-departments.users') }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#user_id').empty().append('<option value="">Seleccione un usuario</option>');
                            response.users.forEach(function(user) {
                                $('#user_id').append(new Option(
                                    user.full_name + ' (' + user.softland_user + ')',
                                    user.softland_user
                                ));
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar usuarios:', xhr);
                        Swal.fire('Error', 'No se pudieron cargar los usuarios', 'error');
                    }
                });

                // Cargar departamentos
                $.ajax({
                    url: '{{ route('proyectos.user-departments.departments') }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#departamento_codigo').empty().append('<option value="">Seleccione un departamento</option>');
                            response.departments.forEach(function(dept) {
                                $('#departamento_codigo').append(new Option(
                                    dept.name,
                                    dept.code
                                ));
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar departamentos:', xhr);
                        Swal.fire('Error', 'No se pudieron cargar los departamentos', 'error');
                    }
                });
            });

            // Guardar asignación
            $('#btn-save-assignment').on('click', function() {
                // Validar que se hayan seleccionado usuario y departamento
                if (!$('#user_id').val()) {
                    Swal.fire('Error', 'Debe seleccionar un usuario', 'error');
                    return;
                }
                if (!$('#departamento_codigo').val()) {
                    Swal.fire('Error', 'Debe seleccionar un departamento', 'error');
                    return;
                }

                $.ajax({
                    url: '{{ route('proyectos.user-departments.store') }}',
                    type: 'POST',
                    data: {
                        softland_user: $('#user_id').val(),
                        departamento_codigo: $('#departamento_codigo').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#modalAssignment').modal('hide');
                        Swal.fire('Éxito', response.message, 'success');
                        userDepartmentsTable.draw();
                        $('#form-assignment')[0].reset();
                        $('#user_id').val(null).trigger('change');
                        $('#departamento_codigo').val(null).trigger('change');
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON?.message || 'Error al guardar la asignación';
                        Swal.fire('Error', message, 'error');
                    }
                });
            });

            // Limpiar formulario al cerrar modal
            $('#modalAssignment').on('hidden.bs.modal', function() {
                $('#form-assignment')[0].reset();
                $('#user_id').val(null).trigger('change');
                $('#departamento_codigo').val(null).trigger('change');
            });

            // Eliminar asignación
            $(document).on('click', '.delete-assignment', function() {
                var usuario = $(this).data('usuario');
                var departamento = $(this).data('departamento');

                Swal.fire({
                    title: '¿Eliminar asignación?',
                    text: "Esta acción no se puede revertir",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('proyectos.user-departments.destroy') }}',
                            type: 'POST',
                            data: {
                                usuario: usuario,
                                departamento: departamento,
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire('Eliminado', response.message, 'success');
                                userDepartmentsTable.draw();
                            },
                            error: function(xhr) {
                                var message = xhr.responseJSON?.message || 'Error al eliminar la asignación';
                                Swal.fire('Error', message, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
