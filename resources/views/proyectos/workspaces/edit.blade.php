@extends('adminlte::page')

@section('title', 'Editar Espacio de Trabajo')

@section('content_header')
    <h1>Editar Espacio de Trabajo</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('proyectos.workspaces.update', $workspace->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $workspace->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea name="description" id="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $workspace->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="is_active" id="is_active"
                           class="form-check-input" {{ old('is_active', $workspace->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Activo</label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                    <a href="{{ route('proyectos.workspaces.show', $workspace->id) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop
