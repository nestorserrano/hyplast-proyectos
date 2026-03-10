@extends('adminlte::page')

@section('title', $workspace->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $workspace->name }}</h1>
        <div>
            <a href="{{ route('proyectos.workspaces.edit', $workspace->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('proyectos.workspaces.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Espacio</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Descripción</dt>
                        <dd>{{ $workspace->description ?? 'Sin descripción' }}</dd>

                        <dt>Estado</dt>
                        <dd>
                            @if($workspace->is_active)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </dd>

                        <dt>Creado Por</dt>
                        <dd>{{ $workspace->creator->name ?? 'N/A' }}</dd>

                        <dt>Fecha Creación</dt>
                        <dd>{{ $workspace->created_at->format('d/m/Y H:i') }}</dd>

                        <dt>Última Actualización</dt>
                        <dd>{{ $workspace->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Áreas ({{ $workspace->areas->count() }})</h3>
                    <div class="card-tools">
                        <a href="{{ route('proyectos.areas.create', ['workspace_id' => $workspace->id]) }}"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Nueva Área
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($workspace->areas->count() > 0)
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Departamento</th>
                                    <th>Carpetas</th>
                                    <th>Listas</th>
                                    <th>Tareas</th>
                                    <th>Estado</th>
                                    <th width="80">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workspace->areas as $area)
                                    <tr>
                                        <td>{{ $area->name }}</td>
                                        <td>{{ $area->departmentInfo->DESCRIPCION ?? 'N/A' }}</td>
                                        <td>{{ $area->folders->count() }}</td>
                                        <td>{{ $area->folders->sum(fn($f) => $f->lists->count()) }}</td>
                                        <td>{{ $area->folders->sum(fn($f) => $f->lists->sum(fn($l) => $l->tasks->count())) }}</td>
                                        <td>
                                            @if($area->is_active)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('proyectos.areas.show', $area->id) }}"
                                               class="btn btn-xs btn-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('proyectos.areas.edit', $area->id) }}"
                                               class="btn btn-xs btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-3 text-center text-muted">
                            No hay áreas en este espacio de trabajo
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
