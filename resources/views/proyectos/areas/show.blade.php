@extends('adminlte::page')

@section('title', $area->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $area->name }}</h1>
        <div>
            <a href="{{ route('proyectos.areas.edit', $area->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('proyectos.areas.index') }}" class="btn btn-secondary">
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
                    <h3 class="card-title">Información del Área</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Espacio de Trabajo</dt>
                        <dd>{{ $area->workspace->name }}</dd>

                        <dt>Departamento</dt>
                        <dd>{{ $area->departmentInfo->DESCRIPCION ?? 'No asignado' }}</dd>

                        <dt>Descripción</dt>
                        <dd>{{ $area->description ?? 'Sin descripción' }}</dd>

                        <dt>Estado</dt>
                        <dd>
                            @if($area->is_active)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </dd>

                        <dt>Creado Por</dt>
                        <dd>{{ $area->creator->name ?? 'N/A' }}</dd>

                        <dt>Fecha Creación</dt>
                        <dd>{{ $area->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Carpetas ({{ $area->folders->count() }})</h3>
                    <div class="card-tools">
                        <a href="{{ route('proyectos.folders.create', ['area_id' => $area->id]) }}"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Nueva Carpeta
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($area->folders->count() > 0)
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Listas</th>
                                    <th>Tareas</th>
                                    <th>Estado</th>
                                    <th width="80">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($area->folders as $folder)
                                    <tr>
                                        <td>{{ $folder->name }}</td>
                                        <td>{{ $folder->lists->count() }}</td>
                                        <td>{{ $folder->lists->sum(fn($l) => $l->tasks->count()) }}</td>
                                        <td>
                                            @if($folder->is_active)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('proyectos.folders.show', $folder->id) }}"
                                               class="btn btn-xs btn-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('proyectos.folders.edit', $folder->id) }}"
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
                            No hay carpetas en esta área
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
