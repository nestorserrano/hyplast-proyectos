@extends('adminlte::page')

@section('title', 'Kanban - Tareas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Tablero Kanban</h1>
        <div class="btn-group" role="group">
            <a href="{{ route('proyectos.tasks.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-list"></i> Lista
            </a>
            <a href="{{ route('proyectos.tasks.kanban') }}" class="btn btn-sm btn-outline-primary active">
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
@stop

@section('content')
    <div class="row">
        <!-- Columna: Pendiente -->
        <div class="col-md-3">
            <div class="card bg-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-circle text-secondary"></i> Pendiente
                        <span class="badge badge-light ml-2">{{ $tasks->get('pending', collect())->count() }}</span>
                    </h3>
                </div>
                <div class="card-body p-2 kanban-column" data-status="pending">
                    @forelse($tasks->get('pending', collect()) as $task)
                        @include('proyectos.tasks.partials.kanban-card', ['task' => $task])
                    @empty
                        <p class="text-center text-muted">No hay tareas</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna: En Progreso -->
        <div class="col-md-3">
            <div class="card bg-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-circle text-primary"></i> En Progreso
                        <span class="badge badge-light ml-2">{{ $tasks->get('in_progress', collect())->count() }}</span>
                    </h3>
                </div>
                <div class="card-body p-2 kanban-column" data-status="in_progress">
                    @forelse($tasks->get('in_progress', collect()) as $task)
                        @include('proyectos.tasks.partials.kanban-card', ['task' => $task])
                    @empty
                        <p class="text-center text-muted">No hay tareas</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna: Capacitación -->
        <div class="col-md-3">
            <div class="card bg-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-circle text-info"></i> Capacitación
                        <span class="badge badge-light ml-2">{{ $tasks->get('training', collect())->count() }}</span>
                    </h3>
                </div>
                <div class="card-body p-2 kanban-column" data-status="training">
                    @forelse($tasks->get('training', collect()) as $task)
                        @include('proyectos.tasks.partials.kanban-card', ['task' => $task])
                    @empty
                        <p class="text-center text-muted">No hay tareas</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna: Completada -->
        <div class="col-md-3">
            <div class="card bg-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-circle text-success"></i> Completada
                        <span class="badge badge-light ml-2">{{ $tasks->get('done', collect())->count() }}</span>
                    </h3>
                </div>
                <div class="card-body p-2 kanban-column" data-status="done">
                    @forelse($tasks->get('done', collect()) as $task)
                        @include('proyectos.tasks.partials.kanban-card', ['task' => $task])
                    @empty
                        <p class="text-center text-muted">No hay tareas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .kanban-column {
            min-height: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .kanban-card {
            cursor: move;
            transition: all 0.3s;
        }
        .kanban-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .sortable-ghost {
            opacity: 0.4;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar Sortable en cada columna
            document.querySelectorAll('.kanban-column').forEach(function(column) {
                new Sortable(column, {
                    group: 'kanban',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function(evt) {
                        let taskId = evt.item.dataset.taskId;
                        let newStatus = evt.to.dataset.status;

                        // Actualizar estado en el servidor
                        $.ajax({
                            url: '/proyectos/tasks/' + taskId + '/update-status',
                            type: 'POST',
                            data: {
                                status: newStatus,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                toastr.success('Estado actualizado correctamente');
                                // Actualizar contadores
                                location.reload();
                            },
                            error: function(xhr) {
                                toastr.error('Error al actualizar el estado');
                                // Revertir cambio
                                evt.item.parentNode.removeChild(evt.item);
                                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
