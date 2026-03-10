@extends('adminlte::page')

@section('title', 'Tareas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Gestión de Tareas</h1>
        <a href="{{ route('proyectos.tasks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Tarea
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Mis Tareas</h3>
                <div class="btn-group" role="group">
                    <a href="{{ route('proyectos.tasks.index') }}" class="btn btn-sm btn-outline-primary active">
                        <i class="fas fa-list"></i> Lista
                    </a>
                    <a href="{{ route('proyectos.tasks.kanban') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-columns"></i> Kanban
                    </a>
                    <a href="{{ route('proyectos.tasks.calendar') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-calendar"></i> Calendario
                    </a>
                    <a href="{{ route('proyectos.tasks.hierarchy') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-sitemap"></i> Jerarquía
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Área del Proyecto</label>
                    <select id="filter-area" class="form-control">
                        <option value="">Todas las áreas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Estado</label>
                    <select id="filter-status" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="in_progress">En Progreso</option>
                        <option value="training">Capacitación</option>
                        <option value="done">Completada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Prioridad</label>
                    <select id="filter-priority" class="form-control">
                        <option value="">Todas las prioridades</option>
                        <option value="low">Baja</option>
                        <option value="normal">Normal</option>
                        <option value="high">Alta</option>
                        <option value="urgent">Urgente</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button id="btn-clear-filters" class="btn btn-secondary btn-block">
                        <i class="fas fa-eraser"></i> Limpiar Filtros
                    </button>
                </div>
            </div>

            <!-- Tabla de tareas -->
            <div class="table-responsive">
                <table id="tasks-table" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Tarea</th>
                            <th>Área</th>
                            <th>Espacio de Trabajo</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th>Asignado a</th>
                            <th>Fecha Límite</th>
                            <th>Progreso</th>
                            <th width="150">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@include('scripts.datatables.datatables-tasks')

    <script>
        $(document).ready(function() {

            // Aplicar filtros
            $('#filter-area, #filter-status, #filter-priority').on('change', function() {
                tasksTable.draw();
            });

            // Limpiar filtros
            $('#btn-clear-filters').on('click', function() {
                $('#filter-area, #filter-status, #filter-priority').val('');
                tasksTable.draw();
            });

            // Eliminar tarea
            $(document).on('click', '.delete-task', function() {
                var taskId = $(this).data('id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede revertir",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/proyectos/tasks/' + taskId,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire('Eliminado', response.message, 'success');
                                tasksTable.draw();
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
