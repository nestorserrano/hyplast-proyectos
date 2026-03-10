@extends('adminlte::page')

@section('title', 'Áreas de Proyectos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Áreas de Proyectos</h1>
        <a href="{{ route('proyectos.areas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Área
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="areas-table" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Espacio de Trabajo</th>
                        <th>Departamento</th>
                        <th>Tareas</th>
                        <th>Estado</th>
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
@include('scripts.datatables.datatables-areas')

    <script>
        $(document).ready(function() {
            $(document).on('click', '.delete-area', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: '¿Eliminar área?',
                    text: "Esto eliminará todas las tareas asociadas",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/proyectos/areas/' + id,
                            type: 'DELETE',
                            data: {_token: '{{ csrf_token() }}'},
                            success: function(response) {
                                Swal.fire('Eliminado', response.message, 'success');
                                areasTable.draw();
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
