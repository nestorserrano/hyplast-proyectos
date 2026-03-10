@extends('adminlte::page')

@section('title', 'Nueva Tarea')

@section('content_header')
    <h1>Crear Nueva Tarea</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('proyectos.tasks.store') }}" method="POST">
            @csrf

            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Nombre de la tarea -->
                        <div class="form-group">
                            <label for="name">Nombre de la Tarea <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea name="description" id="description" rows="4"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Área del Proyecto -->
                        <div class="form-group">
                            <label for="area_id">Área del Proyecto <span class="text-danger">*</span></label>
                            <select name="area_id" id="area_id" class="form-control @error('area_id') is-invalid @enderror" required>
                                <option value="">Seleccione un área</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ old('area_id', $selectedAreaId) == $area->id ? 'selected' : '' }}>
                                        {{ $area->workspace->name }} > {{ $area->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('area_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Estado -->
                        <div class="form-group">
                            <label for="status">Estado <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                                <option value="training" {{ old('status') == 'training' ? 'selected' : '' }}>Capacitación</option>
                                <option value="done" {{ old('status') == 'done' ? 'selected' : '' }}>Completada</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Prioridad -->
                        <div class="form-group">
                            <label for="priority">Prioridad <span class="text-danger">*</span></label>
                            <select name="priority" id="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('priority')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Fecha límite -->
                        <div class="form-group">
                            <label for="due_date">Fecha Límite</label>
                            <input type="date" name="due_date" id="due_date"
                                   class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}">
                            @error('due_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Horas estimadas -->
                        <div class="form-group">
                            <label for="estimated_hours">Horas Estimadas</label>
                            <input type="number" name="estimated_hours" id="estimated_hours" min="0" step="0.5"
                                   class="form-control @error('estimated_hours') is-invalid @enderror" value="{{ old('estimated_hours') }}">
                            @error('estimated_hours')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Asignaciones de Departamentos y Usuarios -->
                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-users"></i> Asignar Tarea a Departamentos y Usuarios</h5>
                        <p class="text-muted">Seleccione un departamento para ver sus usuarios y agregar la asignación</p>

                        @if(isset($selectedArea) && $selectedArea && $selectedArea->departamento_softland)
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle"></i>
                                <strong>Departamento del Área:</strong> Esta área pertenece al departamento
                                <strong>{{ $selectedArea->departamento_softland }}</strong>.
                                Puede agregarlo a las asignaciones usando el selector de abajo.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <!-- Selección de departamento -->
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="department_selector">
                                        <i class="fas fa-building"></i> Departamento
                                    </label>
                                    <select id="department_selector" class="form-control">
                                        <option value="">Seleccione un departamento</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Listado de usuarios del departamento -->
                            <div class="col-md-7">
                                <div id="users-section" style="display: none;">
                                    <label>
                                        <i class="fas fa-users"></i> Usuarios del departamento
                                    </label>

                                    <div class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                                        <div class="form-group mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="select_all_users">
                                                <label class="custom-control-label font-weight-bold" for="select_all_users">
                                                    <i class="fas fa-check-double"></i> Seleccionar TODOS los usuarios
                                                </label>
                                            </div>
                                        </div>
                                        <hr>
                                        <div id="users-list">
                                            <!-- Se llenarán los checkboxes de usuarios -->
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-success btn-sm mt-3" id="btn-add-assignment">
                                        <i class="fas fa-plus-circle"></i> Agregar Asignación
                                    </button>
                                </div>

                                <div id="no-users-message" style="display: none;">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        No hay usuarios asignados a este departamento
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de asignaciones agregadas -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6><i class="fas fa-building"></i> Departamentos agregados:</h6>
                        <div id="assignments-list" class="border rounded p-3 bg-light">
                            <p class="text-muted mb-0" id="no-assignments-msg">
                                <i class="fas fa-info-circle"></i> No se han agregado departamentos aún
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-users"></i> Usuarios totales asignados: <span id="total-users-count" class="badge badge-primary">0</span></h6>
                        <div id="all-users-list" class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                            <p class="text-muted mb-0" id="no-users-assigned-msg">
                                <i class="fas fa-info-circle"></i> No hay usuarios asignados aún
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Input oculto para enviar todos los usuarios seleccionados -->
                <input type="hidden" name="assigned_users_data" id="assigned_users_data">
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar Tarea
                </button>
                <a href="{{ route('proyectos.tasks.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>
        .user-checkbox:checked + label {
            font-weight: bold;
            color: #007bff;
        }

        #assignments-list .d-flex {
            transition: all 0.2s;
        }

        #assignments-list .d-flex:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        #users-list {
            max-height: 250px;
            overflow-y: auto;
        }

        #users-list::-webkit-scrollbar,
        #all-users-list::-webkit-scrollbar {
            width: 8px;
        }

        #users-list::-webkit-scrollbar-track,
        #all-users-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        #users-list::-webkit-scrollbar-thumb,
        #all-users-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        #users-list::-webkit-scrollbar-thumb:hover,
        #all-users-list::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .badge {
            font-size: 85%;
            padding: 0.35em 0.65em;
        }

        #all-users-list .d-flex {
            transition: all 0.2s;
        }

        #all-users-list .d-flex:hover {
            background-color: #f8f9fa;
        }

        #total-users-count {
            font-size: 1rem;
            padding: 0.5em 0.75em;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $.noConflict();

            let currentDepartment = null;
            let currentDepartmentUsers = [];
            let assignments = []; // Array de asignaciones { code, name, allUsers, users: [{id, name, email}] }

            // Pre-cargar departamento del área si existe
            @if(isset($selectedArea) && $selectedArea && $selectedArea->departamento_softland)
                // Cargar automáticamente el departamento del área
                let areaDepartmentCode = '{{ $selectedArea->departamento_softland }}';

                $.ajax({
                    url: '{{ route('proyectos.areas.departments') }}',
                    data: { q: areaDepartmentCode },
                    dataType: 'json',
                    async: false,
                    success: function(response) {
                        let dept = response.departments.find(d => d.code === areaDepartmentCode);
                        if (dept) {
                            console.log('Pre-cargando departamento del área:', dept.name);
                            // No agregamos usuarios todavía, solo dejamos el info visual
                            // El usuario decidirá si quiere agregar este departamento con sus usuarios
                        }
                    }
                });
            @endif

            // Inicializar Select2 del departamento
            $('#department_selector').select2({
                theme: 'bootstrap4',
                placeholder: 'Buscar departamento...',
                allowClear: true,
                ajax: {
                    url: '{{ route('proyectos.areas.departments') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.departments.map(function(dept) {
                                return {
                                    id: dept.code,
                                    text: dept.name + ' (' + dept.code + ')'
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });

            // Cuando se selecciona un departamento
            $('#department_selector').on('change', function() {
                let selectedData = $(this).select2('data')[0];

                if (selectedData && selectedData.id) {
                    currentDepartment = {
                        code: selectedData.id,
                        name: selectedData.text
                    };

                    loadDepartmentUsers(selectedData.id);
                } else {
                    // Si se limpia la selección
                    currentDepartment = null;
                    $('#users-section').hide();
                    $('#no-users-message').hide();
                }
            });

            // Cargar usuarios del departamento
            function loadDepartmentUsers(deptCode) {
                $.ajax({
                    url: '{{ route('proyectos.areas.departments.users') }}',
                    data: { department: deptCode },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#users-section').hide();
                        $('#no-users-message').hide();
                        $('#users-list').html('<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando usuarios...</p>');
                    },
                    success: function(response) {
                        if (response.success && response.users) {
                            currentDepartmentUsers = response.users;

                            if (response.users.length > 0) {
                                // Mostrar sección de usuarios
                                $('#users-section').show();
                                $('#no-users-message').hide();

                                // Limpiar checkboxes
                                $('#select_all_users').prop('checked', false);

                                // Crear checkboxes para cada usuario
                                let usersHtml = '';
                                response.users.forEach(function(user) {
                                    usersHtml += `
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input user-checkbox"
                                                   id="user-${user.id}"
                                                   value="${user.id}"
                                                   data-name="${user.name}"
                                                   data-email="${user.email}">
                                            <label class="custom-control-label" for="user-${user.id}">
                                                ${user.name} <small class="text-muted">(${user.email})</small>
                                            </label>
                                        </div>
                                    `;
                                });

                                $('#users-list').html(usersHtml);
                            } else {
                                $('#users-section').hide();
                                $('#no-users-message').show();
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar usuarios:', xhr);
                        alert('Error al cargar usuarios del departamento');
                        $('#users-section').hide();
                    }
                });
            }

            // Seleccionar/deseleccionar todos
            $('#select_all_users').on('change', function() {
                let isChecked = $(this).is(':checked');
                $('.user-checkbox').prop('checked', isChecked);
            });

            // Si se deselecciona algún usuario individual, quitar el check de "Todos"
            $(document).on('change', '.user-checkbox', function() {
                let totalUsers = $('.user-checkbox').length;
                let checkedUsers = $('.user-checkbox:checked').length;

                $('#select_all_users').prop('checked', totalUsers === checkedUsers);
            });

            // Agregar asignación
            $('#btn-add-assignment').on('click', function() {
                if (!currentDepartment) {
                    alert('Debe seleccionar un departamento');
                    return;
                }

                let allUsersChecked = $('#select_all_users').is(':checked');
                let selectedUsers = [];

                if (!allUsersChecked) {
                    // Obtener usuarios seleccionados individualmente
                    $('.user-checkbox:checked').each(function() {
                        let userId = $(this).val();
                        let userName = $(this).data('name');
                        let userEmail = $(this).data('email');

                        selectedUsers.push({
                            id: userId,
                            name: userName,
                            email: userEmail
                        });
                    });

                    if (selectedUsers.length === 0) {
                        alert('Debe seleccionar al menos un usuario o marcar "Seleccionar TODOS"');
                        return;
                    }
                }

                // Verificar si ya existe este departamento
                let existingIndex = assignments.findIndex(a => a.code === currentDepartment.code);

                if (existingIndex !== -1) {
                    if (!confirm('Este departamento ya está agregado. ¿Desea reemplazar la asignación?')) {
                        return;
                    }
                    assignments.splice(existingIndex, 1);
                }

                // Agregar asignación
                assignments.push({
                    code: currentDepartment.code,
                    name: currentDepartment.name,
                    allUsers: allUsersChecked,
                    users: allUsersChecked ? currentDepartmentUsers : selectedUsers
                });

                renderAssignmentsList();
                updateHiddenData();

                // Limpiar selección
                $('#department_selector').val(null).trigger('change');
                $('#users-section').hide();
                $('#select_all_users').prop('checked', false);
                $('.user-checkbox').prop('checked', false);
            });

            // Renderizar lista de asignaciones
            function renderAssignmentsList() {
                // Renderizar departamentos
                if (assignments.length === 0) {
                    $('#no-assignments-msg').show();
                    $('#assignments-list').html(`
                        <p class="text-muted mb-0" id="no-assignments-msg">
                            <i class="fas fa-info-circle"></i> No se han agregado departamentos aún
                        </p>
                    `);
                } else {
                    $('#no-assignments-msg').hide();

                    let html = '';
                    assignments.forEach(function(assignment, index) {
                        let usersInfo = assignment.allUsers
                            ? `<span class="badge badge-primary">TODOS (${assignment.users.length})</span>`
                            : `<span class="badge badge-info">${assignment.users.length} usuario(s)</span>`;

                        html += `
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded bg-white">
                                <div>
                                    <strong><i class="fas fa-building"></i> ${assignment.name.replace(' (' + assignment.code + ')', '')}</strong>
                                    <br>
                                    <small class="text-muted">${usersInfo}</small>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm remove-assignment" data-index="${index}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    });

                    $('#assignments-list').html(html);
                }

                // Renderizar lista consolidada de usuarios únicos
                renderAllUsersList();
            }

            // Renderizar lista consolidada de todos los usuarios únicos
            function renderAllUsersList() {
                // Consolidar todos los usuarios de todos los departamentos (sin duplicados)
                let allUsers = [];
                let userIds = new Set();

                assignments.forEach(function(assignment) {
                    assignment.users.forEach(function(user) {
                        if (!userIds.has(user.id)) {
                            userIds.add(user.id);
                            allUsers.push(user);
                        }
                    });
                });

                // Ordenar por nombre
                allUsers.sort((a, b) => a.name.localeCompare(b.name));

                // Actualizar contador
                $('#total-users-count').text(allUsers.length);

                // Renderizar lista
                if (allUsers.length === 0) {
                    $('#no-users-assigned-msg').show();
                    $('#all-users-list').html(`
                        <p class="text-muted mb-0" id="no-users-assigned-msg">
                            <i class="fas fa-info-circle"></i> No hay usuarios asignados aún
                        </p>
                    `);
                } else {
                    $('#no-users-assigned-msg').hide();

                    let html = '';
                    allUsers.forEach(function(user) {
                        html += `
                            <div class="d-flex align-items-center mb-2 p-2 border-bottom">
                                <div class="mr-2">
                                    <i class="fas fa-user-circle text-primary"></i>
                                </div>
                                <div>
                                    <strong>${user.name}</strong>
                                    <br>
                                    <small class="text-muted">${user.email}</small>
                                </div>
                            </div>
                        `;
                    });

                    $('#all-users-list').html(html);
                }
            }

            // Eliminar asignación
            $(document).on('click', '.remove-assignment', function() {
                let index = $(this).data('index');

                if (confirm('¿Está seguro de eliminar esta asignación?')) {
                    assignments.splice(index, 1);
                    renderAssignmentsList();
                    updateHiddenData();
                }
            });

            // Actualizar campo hidden
            function updateHiddenData() {
                let data = assignments.map(function(assignment) {
                    return {
                        department: assignment.code,
                        allUsers: assignment.allUsers,
                        users: assignment.users.map(u => u.id)
                    };
                });

                $('#assigned_users_data').val(JSON.stringify(data));
            }

            // Inicializar Select2 en área
            $('#area_id').select2({
                theme: 'bootstrap4'
            });

            // Validar antes de enviar
            $('form').on('submit', function(e) {
                if (assignments.length === 0) {
                    e.preventDefault();
                    alert('Debe agregar al menos una asignación de departamento');
                    return false;
                }

                // Actualizar hidden data antes de enviar
                updateHiddenData();
            });
        });
    </script>
@stop
