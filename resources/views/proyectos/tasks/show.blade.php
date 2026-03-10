@extends('adminlte::page')

@section('template_fastload_css')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/switch.css') }}">
@endsection

@section('title', 'Detalle de Tarea')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $task->name }}</h1>
        <div>
            <a href="{{ route('proyectos.tasks.edit', $task->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('proyectos.tasks.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- Información principal -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información de la Tarea</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Ubicación:</strong><br>
                            {{ $task->area->workspace->name }} > {{ $task->area->name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Estado:</strong><br>
                            <span id="taskStatusBadge" class="badge badge-{{ $task->status == 'done' ? 'success' : ($task->status == 'in_progress' ? 'primary' : 'secondary') }}">
                                {{ $task->status_label }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Prioridad:</strong><br>
                            <span class="badge badge-{{ $task->priority == 'urgent' ? 'danger' : 'secondary' }}">
                                {{ $task->priority_label }}
                            </span>
                        </div>
                    </div>

                    @if($task->description)
                        <div class="mb-3">
                            <strong>Descripción:</strong>
                            <p>{{ $task->description }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                            <strong>Fecha Límite:</strong><br>
                            @if($task->due_date)
                                <span class="{{ $task->is_overdue ? 'text-danger font-weight-bold' : '' }}">
                                    {{ $task->due_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted">Sin fecha límite</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <strong>Horas Estimadas:</strong><br>
                            {{ $task->estimated_hours ?? 0 }} horas
                        </div>
                        <div class="col-md-3">
                            <strong>Horas Reales:</strong><br>
                            <span id="actualHoursDisplay" class="badge badge-info badge-lg">
                                {{ round($task->actual_hours ?? 0, 2) }} horas
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Progreso:</strong><br>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $task->getCompletionPercentage() }}%">
                                    {{ $task->getCompletionPercentage() }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actividades del Checklist -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Actividades</h3>
                        <button type="button"
                                class="btn btn-success btn-sm"
                                id="btnAddActivity">
                            <i class="fas fa-plus"></i> Agregar Actividad
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($task->checklistItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th width="80px" class="text-center">Estado</th>
                                        <th>Actividad</th>
                                        <th width="20%">Responsable</th>
                                        <th width="15%">Tiempo</th>
                                        <th width="100px" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="checklistItemsContainer">
                                    @foreach($task->checklistItems as $item)
                                        <tr id="item-{{ $item->id }}" class="{{ $item->is_completed ? 'text-muted' : '' }}">
                                            <td class="text-center align-middle">
                                                <label class="switch switch-checklist" data-item-id="{{ $item->id }}">
                                                    <input type="checkbox"
                                                           class="checklist-toggle"
                                                           id="checkbox-{{ $item->id }}"
                                                           data-item-id="{{ $item->id }}"
                                                           {{ !$item->is_completed ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <strong>{{ $item->name }}</strong>
                                                @if($item->description)
                                                    <br><small class="text-muted">{{ $item->description }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->assignedUser)
                                                    <i class="fas fa-user"></i> {{ $item->assignedUser->name }}
                                                @else
                                                    <span class="text-muted">Sin asignar</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->is_completed && $item->duration_hours && $item->duration_hours > 0)
                                                    <span class="badge badge-success">
                                                        {{ $item->getFormattedDuration() }}
                                                    </span>
                                                @elseif(!$item->is_completed)
                                                    <span class="badge badge-warning">En proceso</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <button class="btn btn-xs btn-primary btn-edit-item"
                                                        data-item-id="{{ $item->id }}"
                                                        data-name="{{ $item->name }}"
                                                        data-description="{{ $item->description }}"
                                                        data-assigned="{{ $item->assigned_to }}"
                                                        data-started="{{ $item->started_at ? '1' : '0' }}"
                                                        data-completed="{{ $item->is_completed ? '1' : '0' }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-xs btn-danger btn-delete-item"
                                                        data-item-id="{{ $item->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Tiempo Total:</strong></td>
                                        <td colspan="2">
                                            <span class="badge badge-success badge-lg" id="totalHoursDisplay">
                                                {{ round($task->getChecklistHours(), 2) }} horas
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No hay actividades registradas</p>
                    @endif
                </div>
            </div>

            <!-- Actividad -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actividad</h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($task->activities as $activity)
                            <div>
                                <i class="fas fa-circle bg-info"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fas fa-clock"></i> {{ $activity->created_at->diffForHumans() }}
                                    </span>
                                    <h3 class="timeline-header">
                                        {{ $activity->user->name }} {{ $activity->description }}
                                    </h3>
                                    @if($activity->changes)
                                        <div class="timeline-body">
                                            <small class="text-muted">
                                                @foreach($activity->changes as $key => $change)
                                                    <strong>{{ $key }}:</strong> {{ $change['old'] ?? '-' }} → {{ $change['new'] ?? '-' }}<br>
                                                @endforeach
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Asignaciones -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Asignaciones</h3>
                </div>
                <div class="card-body">
                    <strong>Usuarios:</strong>
                    @if($task->assignedUsers->count() > 0)
                        <ul class="list-unstyled">
                            @foreach($task->assignedUsers as $user)
                                <li><i class="fas fa-user"></i> {{ $user->name }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">Sin usuarios asignados</p>
                    @endif

                    <strong>Departamentos:</strong>
                    @if($task->departmentAssignments->count() > 0)
                        <ul class="list-unstyled">
                            @foreach($task->departmentAssignments as $dept)
                                <li><i class="fas fa-building"></i> {{ $dept->departamento_codigo }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">Sin departamentos asignados</p>
                    @endif
                </div>
            </div>

            <!-- Tiempo registrado -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tiempo Registrado</h3>
                </div>
                <div class="card-body">
                    <h4 class="text-center">{{ $task->getTotalTimeSpent() }} minutos</h4>
                    @if($task->timeEntries->count() > 0)
                        <ul class="list-unstyled">
                            @foreach($task->timeEntries as $entry)
                                <li class="mb-2">
                                    <small>
                                        <strong>{{ $entry->user->name }}</strong><br>
                                        {{ $entry->start_time->format('d/m/Y H:i') }} -
                                        {{ $entry->end_time ? $entry->end_time->format('H:i') : 'En curso' }}<br>
                                        {{ $entry->duration_minutes }} min
                                        @if($entry->is_billable)
                                            <span class="badge badge-success badge-sm">Facturable</span>
                                        @endif
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Etiquetas -->
            @if($task->tags->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Etiquetas</h3>
                    </div>
                    <div class="card-body">
                        @foreach($task->tags as $tag)
                            <span class="badge badge-lg mr-1" style="background-color: {{ $tag->color }}; color: white;">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal para Agregar/Editar Actividad -->
    <div class="modal fade" id="activityModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="activityModalTitle">Agregar Actividad</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="activityForm">
                        <input type="hidden" id="itemId" name="item_id">
                        <div class="form-group">
                            <label for="itemName">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="itemName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="itemDescription">Descripción</label>
                            <textarea class="form-control" id="itemDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="itemAssignedTo">Responsable</label>
                            <select class="form-control" id="itemAssignedTo" name="assigned_to">
                                <option value="">Sin asignar</option>
                                @foreach($allUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->softland_user }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="statusGroup" style="display: none;">
                            <label for="itemStatus">Estado de la Actividad</label>
                            <select class="form-control" id="itemStatus" name="status">
                                <option value="pending">Pendiente</option>
                                <option value="completed">Finalizada</option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>Pendiente:</strong> Actividad sin iniciar (sin tiempo registrado)<br>
                                <strong>Finalizada:</strong> Actividad completada (calcula el tiempo automáticamente)
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSaveActivity">Guardar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>

    /* Centrar botones de acción */
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        console.log('=== Script de tareas cargado ===');
        console.log('jQuery version:', $.fn.jquery);

        const taskId = {{ $task->id }};
        console.log('Task ID:', taskId);

        // Evento click en botón editar (alternativo a data-toggle)
        $(document).on('click', '.btn-edit-item', function(e) {
            e.preventDefault();
            const button = $(this);

            // Obtener datos del botón
            const itemId = button.data('item-id');
            const name = button.data('name');
            const description = button.data('description');
            const assigned = button.data('assigned');
            const started = button.data('started');
            const completed = button.data('completed');

            console.log('Editando item:', {itemId, name, description, assigned, started, completed});

            // Configurar modal para editar
            $('#activityModalTitle').text('Editar Actividad');
            $('#itemId').val(itemId);
            $('#itemName').val(name);
            $('#itemDescription').val(description || '');
            $('#itemAssignedTo').val(assigned || '');

            // Mostrar y configurar campo de estado
            $('#statusGroup').show();

            // Determinar el estado actual (solo 2 estados: pending o completed)
            if (completed == '1') {
                $('#itemStatus').val('completed');
            } else {
                $('#itemStatus').val('pending');
            }

            // Abrir modal usando Bootstrap API nativa
            const modalElement = document.getElementById('activityModal');
            const modalInstance = new bootstrap.Modal(modalElement);
            modalInstance.show();
        });

        // Evento click en botón agregar
        $('#btnAddActivity').on('click', function(e) {
            e.preventDefault();

            // Configurar modal para agregar
            $('#activityModalTitle').text('Agregar Actividad');
            $('#activityForm')[0].reset();
            $('#itemId').val('');
            $('#statusGroup').hide();

            // Abrir modal usando Bootstrap API nativa
            const modalElement = document.getElementById('activityModal');
            const modalInstance = new bootstrap.Modal(modalElement);
            modalInstance.show();
        });

        // Guardar actividad (create/update)
        $('#btnSaveActivity').click(function(e) {
            e.preventDefault();
            console.log('Guardando actividad');

            const itemId = $('#itemId').val();
            const formData = {
                name: $('#itemName').val(),
                description: $('#itemDescription').val(),
                assigned_to: $('#itemAssignedTo').val() || null,
                _token: '{{ csrf_token() }}'
            };

            // Si es edición, incluir el estado
            if (itemId) {
                formData.status = $('#itemStatus').val();
            }

            // Validar que el nombre no esté vacío
            if (!formData.name) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'El nombre de la actividad es requerido'
                });
                return;
            }

            let url, method;
            if (itemId) {
                // Editar
                url = `/proyectos/tasks/${taskId}/checklist/${itemId}`;
                method = 'POST';
                formData._method = 'PUT';  // Agregar _method para Laravel
            } else {
                // Crear
                url = `/proyectos/tasks/${taskId}/checklist`;
                method = 'POST';
            }

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Cerrar modal usando el botón de cerrar o simplemente recargar
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    console.log('Error:', xhr);  // Para debugging
                    console.log('Response:', xhr.responseJSON);
                    console.log('Status:', xhr.status);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al guardar la actividad'
                    });
                }
            });
        });

        // Toggle completado - Switch simple ON/OFF
        // ON = Completado con tiempo | OFF = Pendiente sin tiempo
        $(document).on('click', '.switch-checklist', function(e) {
            e.preventDefault();
            e.stopPropagation();

            console.log('=== CLICK EN SWITCH ===');

            const label = $(this);
            const checkbox = label.find('.checklist-toggle');
            const itemId = label.data('item-id');

            console.log('Label:', label);
            console.log('Checkbox:', checkbox);
            console.log('Item ID:', itemId);
            console.log('Estado actual checked:', checkbox.prop('checked'));

            // Si el checkbox está deshabilitado, no hacer nada
            if (checkbox.prop('disabled')) {
                console.log('Checkbox deshabilitado, ignorando click');
                return false;
            }
            // Deshabilitar el checkbox mientras se procesa
            checkbox.prop('disabled', true);

            console.log('Enviando request a:', `/proyectos/tasks/${taskId}/checklist/${itemId}/toggle`);

            $.ajax({
                url: `/proyectos/tasks/${taskId}/checklist/${itemId}/toggle`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('=== RESPUESTA EXITOSA ===');
                    console.log(response);
                    console.log('Duration hours:', response.item.duration_hours);
                    console.log('Total hours (checklist):', response.total_hours);
                    console.log('Actual hours (task):', response.actual_hours);

                    if (response.success) {
                        const row = $(`#item-${itemId}`);
                        const timeCell = row.find('td:eq(3)');

                        // Actualizar estado visual según el nuevo estado del servidor
                        // Switch ON = Activa (is_completed = false)
                        // Switch OFF = Finalizada (is_completed = true)
                        if (response.is_completed) {
                            console.log('Estado: FINALIZADA (switch OFF)');
                            console.log('Item duration_hours:', response.item.duration_hours);
                            console.log('Formatted duration:', response.formatted_duration);
                            row.addClass('text-muted');
                            checkbox.prop('checked', false); // Switch OFF cuando está finalizada
                            // Actualizar celda de tiempo con la duración
                            if (response.item.duration_hours && response.item.duration_hours > 0) {
                                console.log('Mostrando tiempo en badge:', response.formatted_duration);
                                timeCell.html('<span class="badge badge-success">' + response.formatted_duration + '</span>');
                            } else {
                                console.log('Sin tiempo registrado o duration_hours = 0');
                                timeCell.html('<span class="text-muted">-</span>');
                            }
                        } else {
                            console.log('Estado: ACTIVA/EN PROCESO (switch ON)');
                            row.removeClass('text-muted');
                            checkbox.prop('checked', true); // Switch ON cuando está activa
                            timeCell.html('<span class="badge badge-warning">En proceso</span>');
                        }

                        // Actualizar progreso de la tarea
                        if (response.task_progress !== undefined) {
                            $('.progress-bar').css('width', response.task_progress + '%')
                                              .attr('aria-valuenow', response.task_progress)
                                              .text(response.task_progress + '%');
                        }

                        // Actualizar estado de la tarea
                        if (response.task_status !== undefined) {
                            const statusBadge = $('#taskStatusBadge');
                            const statusLabels = {
                                'pending': 'Pendiente',
                                'in_progress': 'En Progreso',
                                'done': 'Completada'
                            };
                            const statusColors = {
                                'pending': 'secondary',
                                'in_progress': 'primary',
                                'done': 'success'
                            };

                            statusBadge.removeClass('badge-secondary badge-primary badge-success')
                                      .addClass('badge-' + statusColors[response.task_status])
                                      .text(statusLabels[response.task_status]);
                        }

                        // Actualizar tiempo total del checklist
                        if (response.total_hours !== undefined) {
                            $('#totalHoursDisplay').text(response.total_hours + ' horas');
                            console.log('Tiempo total checklist actualizado:', response.total_hours, 'horas');
                        }

                        // Actualizar horas reales de la tarea
                        if (response.actual_hours !== undefined) {
                            $('#actualHoursDisplay').text(response.actual_hours + ' horas');
                            console.log('Horas reales tarea actualizadas:', response.actual_hours, 'horas');
                        }

                        // Habilitar el checkbox nuevamente
                        checkbox.prop('disabled', false);

                        // Mostrar notificación breve
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });

                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                    }
                },
                error: function(xhr) {
                    // Revertir el checkbox y habilitarlo si hay error
                    const wasChecked = checkbox.prop('checked');
                    checkbox.prop('checked', !wasChecked);
                    checkbox.prop('disabled', false);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al actualizar el estado'
                    });
                }
            });
        });

        // Eliminar actividad
        $(document).on('click', '.btn-delete-item', function() {
            const itemId = $(this).data('item-id');

            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede revertir",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/proyectos/tasks/${taskId}/checklist/${itemId}`,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: response.message,
                                    timer: 1500
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            console.log('Error:', xhr);  // Para debugging
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Error al eliminar la actividad'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@stop

