@extends('adminlte::page')

@section('title', 'Vista Jerárquica - Tareas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Vista Jerárquica de Tareas</h1>
        <div class="btn-group" role="group">
            <a href="{{ route('proyectos.tasks.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-list"></i> Lista
            </a>
            <a href="{{ route('proyectos.tasks.kanban') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-columns"></i> Kanban
            </a>
            <a href="{{ route('proyectos.tasks.calendar') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-calendar"></i> Calendario
            </a>
            <a href="{{ route('proyectos.tasks.hierarchy') }}" class="btn btn-sm btn-outline-primary active">
                <i class="fas fa-sitemap"></i> Jerarquía
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary" id="expand-all">
                    <i class="fas fa-plus-circle"></i> Expandir Todo
                </button>
                <button type="button" class="btn btn-outline-secondary" id="collapse-all">
                    <i class="fas fa-minus-circle"></i> Colapsar Todo
                </button>
            </div>
        </div>
    </div>

    @foreach($workspaces as $workspace)
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <button class="btn btn-link text-white workspace-toggle" type="button" data-toggle="collapse"
                            data-target="#workspace-{{ $workspace->id }}" aria-expanded="true">
                        <i class="fas fa-folder-open toggle-icon"></i>
                        <strong>{{ $workspace->name }}</strong>
                        <span class="badge badge-light ml-2">{{ $workspace->areas->sum(function($area) { return $area->tasks->count(); }) }} tareas</span>
                    </button>
                </h5>
            </div>
            <div class="collapse show" id="workspace-{{ $workspace->id }}">
                <div class="card-body p-0">
                    @if($workspace->areas->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($workspace->areas as $area)
                                <div class="list-group-item p-0">
                                    <div class="area-header" style="padding: 12px 20px; background-color: #f8f9fa; border-left: 4px solid {{ $area->color ?? '#10B981' }};">
                                        <button class="btn btn-link area-toggle p-0 text-dark" type="button"
                                                data-toggle="collapse" data-target="#area-{{ $area->id }}">
                                            <i class="fas fa-folder toggle-icon"></i>
                                            <strong>{{ $area->name }}</strong>
                                            @if($area->departamento_softland)
                                                <span class="badge badge-info ml-2">{{ $area->departamento_softland }}</span>
                                            @endif
                                            <span class="badge badge-secondary ml-2">{{ $area->tasks->count() }} tareas</span>
                                        </button>
                                        @if($area->description)
                                            <small class="text-muted d-block mt-1">{{ Str::limit($area->description, 80) }}</small>
                                        @endif
                                    </div>

                                    <div class="collapse" id="area-{{ $area->id }}">
                                        @if($area->tasks->count() > 0)
                                            <div class="tasks-list" style="padding-left: 40px;">
                                                @foreach($area->tasks as $task)
                                                    <div class="task-item border-bottom py-2 px-3" style="background-color: #ffffff;">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <a href="{{ route('proyectos.tasks.show', $task->id) }}" class="text-dark font-weight-bold">
                                                                    <i class="fas fa-tasks text-muted mr-2"></i>
                                                                    {{ $task->name }}
                                                                </a>

                                                                <div class="mt-1">
                                                                    <span class="badge badge-{{ $task->status == 'done' ? 'success' : ($task->status == 'in_progress' ? 'primary' : 'secondary') }}">
                                                                        {{ $task->status_label }}
                                                                    </span>
                                                                    <span class="badge badge-{{ $task->priority == 'urgent' ? 'danger' : ($task->priority == 'high' ? 'warning' : 'secondary') }}">
                                                                        {{ $task->priority_label }}
                                                                    </span>

                                                                    @if($task->due_date)
                                                                        <small class="text-muted ml-2">
                                                                            <i class="fas fa-calendar"></i> {{ $task->due_date->format('d/m/Y') }}
                                                                            @if($task->is_overdue)
                                                                                <span class="text-danger font-weight-bold">(Vencida)</span>
                                                                            @endif
                                                                        </small>
                                                                    @endif

                                                                    @if($task->assignedUsers->count() > 0)
                                                                        <small class="text-muted ml-2">
                                                                            <i class="fas fa-users"></i> {{ $task->assignedUsers->count() }}
                                                                        </small>
                                                                    @endif
                                                                </div>

                                                                @if($task->description)
                                                                    <small class="text-muted d-block mt-1">{{ Str::limit($task->description, 100) }}</small>
                                                                @endif
                                                            </div>

                                                            <div class="ml-3">
                                                                <div class="progress" style="width: 80px; height: 20px;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                         style="width: {{ $task->progress }}%;"
                                                                         aria-valuenow="{{ $task->progress }}" aria-valuemin="0" aria-valuemax="100">
                                                                        {{ $task->progress }}%
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-3">
                                                <i class="fas fa-inbox"></i> No hay tareas en esta área
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-inbox"></i> No hay áreas en este espacio de trabajo
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    @if($workspaces->count() == 0)
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-folder-open fa-3x mb-3"></i>
                <h4>No hay espacios de trabajo disponibles</h4>
                <p>Contacte al administrador para crear espacios de trabajo.</p>
            </div>
        </div>
    @endif
@stop

@section('css')
    <style>
        .workspace-toggle, .area-toggle {
            text-decoration: none !important;
            transition: all 0.3s;
        }

        .workspace-toggle:hover, .area-toggle:hover {
            opacity: 0.8;
        }

        .toggle-icon {
            transition: transform 0.3s;
        }

        .collapsed .toggle-icon {
            transform: rotate(-90deg);
        }

        .task-item {
            transition: all 0.2s;
        }

        .task-item:hover {
            background-color: #f8f9fa !important;
            transform: translateX(5px);
        }

        .area-header {
            transition: background-color 0.2s;
        }

        .area-header:hover {
            background-color: #e9ecef !important;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Expandir todo
            $('#expand-all').on('click', function() {
                $('.collapse').collapse('show');
                $('.toggle-icon').removeClass('fa-folder').addClass('fa-folder-open');
            });

            // Colapsar todo
            $('#collapse-all').on('click', function() {
                $('.collapse').collapse('hide');
                $('.toggle-icon').removeClass('fa-folder-open').addClass('fa-folder');
            });

            // Cambiar icono al colapsar/expandir workspaces
            $('.workspace-toggle').on('click', function() {
                var icon = $(this).find('.toggle-icon');
                setTimeout(function() {
                    if (icon.closest('.card-header').next('.collapse').hasClass('show')) {
                        icon.removeClass('fa-folder').addClass('fa-folder-open');
                    } else {
                        icon.removeClass('fa-folder-open').addClass('fa-folder');
                    }
                }, 350);
            });

            // Cambiar icono al colapsar/expandir áreas
            $('.area-toggle').on('click', function() {
                var icon = $(this).find('.toggle-icon');
                setTimeout(function() {
                    var target = icon.closest('button').data('target');
                    if ($(target).hasClass('show')) {
                        icon.removeClass('fa-folder').addClass('fa-folder-open');
                    } else {
                        icon.removeClass('fa-folder-open').addClass('fa-folder');
                    }
                }, 350);
            });
        });
    </script>
@stop
