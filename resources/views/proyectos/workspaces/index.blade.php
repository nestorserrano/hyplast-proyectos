@extends('adminlte::page')

@section('title', 'Espacios de Trabajo')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Espacios de Trabajo</h1>
        <a href="{{ route('proyectos.workspaces.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Espacio
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="workspaces-table" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Creado Por</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th width="120">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@include('scripts.datatables.datatables-workspaces')

    <script>
        $(document).ready(function() {
            $(document).on('click', '.delete-workspace', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: '¿Eliminar espacio de trabajo?',
                    text: "Esto eliminará todas las áreas, carpetas, listas y tareas asociadas",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/proyectos/workspaces/' + id,
                            type: 'DELETE',
                            data: {_token: '{{ csrf_token() }}'},
                            success: function(response) {
                                Swal.fire('Eliminado', response.message, 'success');
                                workspacesTable.draw();
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON.message, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
