@extends('adminlte::page')

@section('title', 'Editar Área')

@section('content_header')
    <h1>Editar Área</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('proyectos.areas.update', $area->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="workspace_id">Espacio de Trabajo <span class="text-danger">*</span></label>
                    <select name="workspace_id" id="workspace_id"
                            class="form-control @error('workspace_id') is-invalid @enderror" required>
                        @foreach($workspaces as $workspace)
                            <option value="{{ $workspace->id }}"
                                    {{ old('workspace_id', $area->workspace_id) == $workspace->id ? 'selected' : '' }}>
                                {{ $workspace->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('workspace_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $area->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="departamento_softland">
                        Departamento Softland
                        <i class="fas fa-info-circle text-info" data-toggle="tooltip"
                           title="Departamento al que pertenece esta área organizacionalmente. Las tareas pueden asignarse a múltiples departamentos adicionales."></i>
                    </label>
                    <select name="departamento_softland" id="departamento_softland"
                            class="form-control @error('departamento_softland') is-invalid @enderror">
                        <option value="">Seleccione un departamento (opcional)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->code }}"
                                    {{ old('departamento_softland', $area->departamento_softland) == $dept->code ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">
                        Define el departamento propietario de esta área para organización jerárquica.
                    </small>
                    @error('departamento_softland')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea name="description" id="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $area->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="is_active" id="is_active"
                           class="form-check-input" {{ old('is_active', $area->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Activo</label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                    <a href="{{ route('proyectos.areas.show', $area->id) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $.noConflict();

            $('#workspace_id, #departamento_softland').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
            });
        });
    </script>
@stop
