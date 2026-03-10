<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\ProjectArea;
use App\Models\Workspace;
use App\Models\User;
use App\Models\UserDepartment;
use App\Models\TaskDepartmentAssignment;
use App\Helpers\SchemaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tasks = $this->getTasksQuery($request);

            return Datatables::of($tasks)
                ->addIndexColumn()
                ->addColumn('area_name', function($task) {
                    // Lookup manual del área
                    $area = ProjectArea::find($task->area_id);
                    return $area ? $area->name : '-';
                })
                ->addColumn('workspace_name', function($task) {
                    // Lookup manual del workspace vía área
                    $area = ProjectArea::find($task->area_id);
                    if (!$area) return '-';
                    $workspace = $area->workspace;
                    return $workspace ? $workspace->name : '-';
                })
                ->addColumn('status_badge', function($task) {
                    $badges = [
                        'pending' => '<span class="badge badge-secondary">Pendiente</span>',
                        'in_progress' => '<span class="badge badge-primary">En Progreso</span>',
                        'training' => '<span class="badge badge-info">Capacitación</span>',
                        'done' => '<span class="badge badge-success">Completada</span>',
                    ];
                    return $badges[$task->status] ?? '<span class="badge badge-secondary">-</span>';
                })
                ->addColumn('priority_badge', function($task) {
                    $badges = [
                        'urgent' => '<span class="badge badge-danger">Urgente</span>',
                        'high' => '<span class="badge badge-warning">Alta</span>',
                        'medium' => '<span class="badge badge-info">Media</span>',
                        'low' => '<span class="badge badge-secondary">Baja</span>',
                    ];
                    return $badges[$task->priority] ?? '<span class="badge badge-secondary">-</span>';
                })
                ->addColumn('assigned_to', function($task) {
                    // Lookup manual de usuarios asignados
                    $userIds = \DB::table('task_assignments')
                        ->where('task_id', $task->id)
                        ->pluck('user_id');

                    if ($userIds->isEmpty()) {
                        return '<span class="text-muted">Sin asignar</span>';
                    }

                    $users = User::whereIn('id', $userIds)->pluck('first_name')->toArray();
                    return implode(', ', array_slice($users, 0, 2)) . (count($users) > 2 ? '...' : '');
                })
                ->editColumn('due_date', function($task) {
                    if (!$task->due_date) return '<span class="text-muted">Sin fecha</span>';
                    $class = $task->is_overdue && $task->status != 'done' ? 'text-danger font-weight-bold' : '';
                    return '<span class="'.$class.'">'.$task->due_date->format('d/m/Y').'</span>';
                })
                ->addColumn('progress', function($task) {
                    $color = $task->progress == 100 ? 'success' : 'primary';
                    return '<div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-'.$color.'" style="width: '.$task->progress.'%">'.$task->progress.'%</div>
                    </div>';
                })
                ->addColumn('action', function($task) {
                    $showBtn = '<a href="'.route('proyectos.tasks.show', $task->id).'" class="btn btn-info btn-sm" title="Ver"><i class="fas fa-eye"></i></a>';
                    $editBtn = '<a href="'.route('proyectos.tasks.edit', $task->id).'" class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>';
                    $deleteBtn = '<button class="btn btn-danger btn-sm delete-task" data-id="'.$task->id.'" title="Eliminar"><i class="fas fa-trash"></i></button>';
                    return $showBtn.' '.$editBtn.' '.$deleteBtn;
                })
                ->rawColumns(['status_badge', 'priority_badge', 'assigned_to', 'due_date', 'progress', 'action'])
                ->make(true);
        }

        $areas = ProjectArea::where('is_active', true)->get();
        return view('proyectos.tasks.index', compact('areas'));
    }

    protected function getTasksQuery($request)
    {
        // No usar eager loading - causa errores de cross-connection
        $query = Task::query();

        // Filtrar solo tareas del usuario actual (asignadas directamente o por departamento)
        $userId = auth()->id();

        $userDepartments = [];
        if (auth()->user()->softland_user) {
            $userDepartments = UserDepartment::where('USUARIO', auth()->user()->softland_user)
                ->pluck('DEPARTAMENTO')
                ->toArray();
        }

        $query->where(function($q) use ($userId, $userDepartments) {
            // Tareas asignadas directamente al usuario
            $q->whereHas('assignedUsers', function($sub) use ($userId) {
                $sub->where('user_id', $userId);
            })
            // O tareas asignadas a departamentos del usuario
            ->orWhereHas('departmentAssignments', function($sub) use ($userDepartments) {
                if (!empty($userDepartments)) {
                    $sub->whereIn('departamento_codigo', $userDepartments);
                }
            })
            // O tareas creadas por el usuario
            ->orWhere('created_by', $userId);
        });

        // Aplicar filtros
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        return $query->get();
    }

    public function create(Request $request)
    {
        // Cargar áreas activas (estructura simplificada)
        $areas = ProjectArea::where('is_active', true)->get();
        $selectedAreaId = $request->get('area_id');

        // Si hay un área seleccionada, cargarla con su departamento
        $selectedArea = null;
        if ($selectedAreaId) {
            $selectedArea = ProjectArea::find($selectedAreaId);
        }

        return view('proyectos.tasks.create', compact('areas', 'selectedAreaId', 'selectedArea'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:project_areas,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_users_data' => 'required|string', // JSON con estructura de departamentos y usuarios
            'assigned_departments' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Crear la tarea
            $task = Task::create([
                'area_id' => $request->area_id,
                'conjunto_id' => SchemaHelper::getSchema(),
                'name' => $request->name,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => 'pending',
                'due_date' => $request->due_date,
                'progress' => 0,
                'created_by' => auth()->id(),
            ]);

            // Procesar asignaciones desde el JSON
            $usersData = json_decode($request->assigned_users_data, true);
            $allAssignedUsers = []; // Array para guardar todos los usuarios asignados y notificarlos
            $schema = SchemaHelper::getSchema();

            foreach ($usersData as $deptData) {
                $deptCode = $deptData['department'];
                $allUsers = $deptData['allUsers'] ?? false;
                $selectedUsers = $deptData['users'] ?? [];

                // Crear asignación de departamento
                $task->departmentAssignments()->create([
                    'departamento_codigo' => $deptCode,
                    'conjunto_id' => $schema,
                    'assigned_by' => auth()->id(),
                ]);

                // Determinar qué usuarios asignar
                $usersToAssign = [];

                if ($allUsers) {
                    // Obtener todos los usuarios del departamento
                    $softlandUsers = DB::connection('softland')
                        ->table("{$schema}.usuarios_departamento")
                        ->where('DEPARTAMENTO', $deptCode)
                        ->pluck('USUARIO');

                    $usersToAssign = User::whereIn('softland_user', $softlandUsers)
                        ->pluck('id')
                        ->toArray();
                } else {
                    // Usar usuarios seleccionados específicamente
                    $usersToAssign = $selectedUsers;
                }

                // Asignar usuarios a la tarea (sin duplicados)
                foreach ($usersToAssign as $userId) {
                    if (!in_array($userId, $allAssignedUsers)) {
                        $allAssignedUsers[] = $userId;
                    }
                }
            }

            // Asignar todos los usuarios a la tarea (sin duplicados)
            if (!empty($allAssignedUsers)) {
                // Preparar array con datos adicionales para la tabla pivot
                $pivotData = [];
                foreach ($allAssignedUsers as $userId) {
                    $pivotData[$userId] = [
                        'assigned_by' => auth()->id(),
                        'assignment_type' => 'individual',
                    ];
                }

                $task->assignedUsers()->attach($pivotData);

                // Enviar notificaciones a cada usuario
                $this->sendTaskAssignmentNotifications($task, $allAssignedUsers);
            }

            DB::commit();

            Alert::success('Éxito', 'Tarea creada y asignada correctamente. Se enviaron notificaciones a los usuarios.');
            return redirect()->route('proyectos.tasks.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error', 'Error al crear tarea: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Enviar notificaciones a usuarios asignados a una tarea
     */
    private function sendTaskAssignmentNotifications($task, $userIds)
    {
        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            $user->notify(new \App\Notifications\TaskAssigned($task));
        }
    }

    public function show($id)
    {
        // Estructura simplificada: solo área y workspace
        $task = Task::with([
                'activities.user',
                'creator',
                'updater',
                'area.workspace',  // Cargar workspace para evitar N+1
                'timeEntries.user',
                'assignedUsers',
                'departmentAssignments',
                'tags',
                'checklistItems.assignedUser'  // Cargar checklist items con usuarios asignados
            ])
            ->findOrFail($id);

        // Verificar acceso
        if (!$this->checkTaskAccess($task)) {
            abort(403, 'No tienes permiso para ver esta tarea');
        }

        // Obtener todos los usuarios con softland_user para el select de actividades
        $allUsers = \App\Models\User::whereNotNull('softland_user')
            ->where('softland_user', '!=', '')
            ->orderBy('name')
            ->get();

        return view('proyectos.tasks.show', compact('task', 'allUsers'));
    }

    public function edit($id)
    {
        $task = Task::with(['assignedUsers', 'departmentAssignments', 'area'])
            ->findOrFail($id);

        if (!$this->checkTaskAccess($task)) {
            abort(403, 'No tienes permiso para editar esta tarea');
        }

        $areas = ProjectArea::where('is_active', true)->get();
        $users = User::orderBy('first_name')->get();

        return view('proyectos.tasks.edit', compact('task', 'areas', 'users'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'area_id' => 'required|exists:project_areas,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,training,done',
            'due_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        try {
            $task = Task::findOrFail($id);

            if (!$this->checkTaskAccess($task)) {
                abort(403, 'No tienes permiso para editar esta tarea');
            }

            $task->update([
                'area_id' => $request->area_id,
                'name' => $request->name,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => $request->status,
                'due_date' => $request->due_date,
                'progress' => $request->progress ?? $task->progress,
            ]);

            // Actualizar usuarios asignados
            if ($request->has('assigned_users')) {
                $task->assignedUsers()->sync($request->assigned_users ?? []);
            }

            Alert::success('Éxito', 'Tarea actualizada correctamente');
            return redirect()->route('proyectos.tasks.show', $id);

        } catch (\Exception $e) {
            Alert::error('Error', 'Error al actualizar: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $task = Task::findOrFail($id);

            if (!$this->checkTaskAccess($task)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar esta tarea'
                ], 403);
            }

            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tarea eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function kanban()
    {
        try {
            $userId = auth()->id();

            // Obtener departamentos del usuario desde Softland
            $userDepartments = [];
            if (auth()->user()->softland_user) {
                $userDepartments = UserDepartment::where('USUARIO', auth()->user()->softland_user)
                    ->pluck('DEPARTAMENTO')
                    ->toArray();
            }

            // No usar eager loading - causa errores de cross-connection
            $tasks = Task::where(function($q) use ($userId, $userDepartments) {
                    $q->whereHas('assignedUsers', function($sub) use ($userId) {
                        $sub->where('user_id', $userId);
                    })
                    ->orWhereHas('departmentAssignments', function($sub) use ($userDepartments) {
                        if (!empty($userDepartments)) {
                            $sub->whereIn('departamento_codigo', $userDepartments);
                        }
                    })
                    ->orWhere('created_by', $userId);
                })
                ->get()
                ->groupBy('status');

            return view('proyectos.tasks.kanban', compact('tasks'));

        } catch (\Exception $e) {
            \Log::error('Error en kanban: ' . $e->getMessage());
            return view('proyectos.tasks.kanban')->with('error', 'Error al cargar las tareas');
        }
    }

    public function calendar()
    {
        return view('proyectos.tasks.calendar');
    }

    public function calendarEvents(Request $request)
    {
        try {
            $userId = auth()->id();

            // Obtener departamentos del usuario desde Softland
            $userDepartments = [];
            if (auth()->user() && auth()->user()->softland_user) {
                $schema = SchemaHelper::getSchema();
                $userDepartments = DB::connection('softland')
                    ->table("{$schema}.usuarios_departamento")
                    ->where('USUARIO', auth()->user()->softland_user)
                    ->pluck('DEPARTAMENTO')
                    ->toArray();
            }

            $tasks = Task::whereNotNull('due_date')
                ->where(function($q) use ($userId, $userDepartments) {
                    // Tareas asignadas directamente al usuario
                    $q->whereHas('assignedUsers', function($sub) use ($userId) {
                        $sub->where('user_id', $userId);
                    });

                    // Tareas de departamentos del usuario (solo si tiene departamentos)
                    if (!empty($userDepartments)) {
                        $q->orWhereHas('departmentAssignments', function($sub) use ($userDepartments) {
                            $sub->whereIn('departamento_codigo', $userDepartments);
                        });
                    }

                    // Tareas creadas por el usuario
                    $q->orWhere('created_by', $userId);
                })
                ->get();

            $events = $tasks->map(function($task) {
                $colors = [
                    'pending' => '#6c757d',
                    'in_progress' => '#007bff',
                    'training' => '#17a2b8',
                    'done' => '#28a745',
                ];

                return [
                    'id' => $task->id,
                    'title' => $task->name,
                    'start' => $task->due_date->format('Y-m-d'),
                    'backgroundColor' => $colors[$task->status] ?? '#6c757d',
                    'borderColor' => $colors[$task->status] ?? '#6c757d',
                    'url' => route('proyectos.tasks.show', $task->id),
                ];
            });

            return response()->json($events);

        } catch (\Exception $e) {
            \Log::error('Error en calendarEvents: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => 'Error al cargar tareas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function hierarchy()
    {
        try {
            $userId = auth()->id();
            $schema = SchemaHelper::getSchema();

            // Obtener departamentos del usuario
            $userDepartments = [];
            if (auth()->user() && auth()->user()->softland_user) {
                $userDepartments = DB::connection('softland')
                    ->table("{$schema}.usuarios_departamento")
                    ->where('USUARIO', auth()->user()->softland_user)
                    ->pluck('DEPARTAMENTO')
                    ->toArray();
            }

            // Cargar workspaces con sus áreas y tareas que el usuario puede ver
            $workspaces = Workspace::with(['areas' => function($query) use ($userId, $userDepartments) {
                $query->where('is_active', true)
                    ->with(['tasks' => function($taskQuery) use ($userId, $userDepartments) {
                        $taskQuery->where(function($q) use ($userId, $userDepartments) {
                            // Tareas asignadas directamente
                            $q->whereHas('assignedUsers', function($sub) use ($userId) {
                                $sub->where('user_id', $userId);
                            });

                            // Tareas de departamentos del usuario
                            if (!empty($userDepartments)) {
                                $q->orWhereHas('departmentAssignments', function($sub) use ($userDepartments) {
                                    $sub->whereIn('departamento_codigo', $userDepartments);
                                });
                            }

                            // Tareas creadas por el usuario
                            $q->orWhere('created_by', $userId);
                        })
                        ->with('assignedUsers')
                        ->orderBy('due_date', 'asc')
                        ->orderBy('priority', 'desc');
                    }]);
            }])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

            return view('proyectos.tasks.hierarchy', compact('workspaces'));

        } catch (\Exception $e) {
            \Log::error('Error en hierarchy: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            Alert::error('Error', 'Error al cargar vista jerárquica');
            return redirect()->route('proyectos.tasks.index');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,training,done',
        ]);

        try {
            $task = Task::findOrFail($id);

            if (!$this->checkTaskAccess($task)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para actualizar esta tarea'
                ], 403);
            }

            $task->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function checkTaskAccess($task)
    {
        $userId = auth()->id();

        // Creador tiene acceso
        if ($task->created_by == $userId) {
            return true;
        }

        // Usuario asignado tiene acceso
        if ($task->assignedUsers()->where('user_id', $userId)->exists()) {
            return true;
        }

        // Usuario en departamento asignado tiene acceso
        $userDepartments = [];
        if (auth()->user()->softland_user) {
            $userDepartments = UserDepartment::where('USUARIO', auth()->user()->softland_user)
                ->pluck('DEPARTAMENTO')
                ->toArray();
        }

        if (!empty($userDepartments) && $task->departmentAssignments()->whereIn('departamento_codigo', $userDepartments)->exists()) {
            return true;
        }

        return false;
    }
}
