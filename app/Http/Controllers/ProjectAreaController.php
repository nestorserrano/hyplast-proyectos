<?php

namespace App\Http\Controllers;

use App\Models\ProjectArea;
use App\Models\Task;
use App\Models\Workspace;
use App\Helpers\SchemaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

class ProjectAreaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // No usar eager loading - causa errores de cross-connection
            $areas = ProjectArea::all();

            return Datatables::of($areas)
                ->addIndexColumn()
                ->addColumn('workspace_name', function($area) {
                    // Lookup manual del workspace
                    $workspace = Workspace::find($area->workspace_id);
                    return $workspace ? $workspace->name : '-';
                })
                ->addColumn('department_name', function($area) {
                    try {
                        // Usar accessor que hace consulta directa a Softland
                        $deptInfo = $area->departmentInfo;
                        return $deptInfo ? $deptInfo->DESCRIPCION : '-';
                    } catch (\Exception $e) {
                        return '-';
                    }
                })
                ->addColumn('tasks_count', function($area) {
                    // Contar tareas directamente en la base de datos
                    return Task::where('area_id', $area->id)->count();
                })
                ->addColumn('status_badge', function($area) {
                    return $area->is_active
                        ? '<span class="badge badge-success">Activo</span>'
                        : '<span class="badge badge-secondary">Inactivo</span>';
                })
                ->addColumn('action', function($area) {
                    $showBtn = '<a href="'.route('proyectos.areas.show', $area->id).'" class="btn btn-info btn-sm" title="Ver"><i class="fas fa-eye"></i></a>';
                    $editBtn = '<a href="'.route('proyectos.areas.edit', $area->id).'" class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>';
                    $deleteBtn = '<button class="btn btn-danger btn-sm delete-area" data-id="'.$area->id.'" title="Eliminar"><i class="fas fa-trash"></i></button>';

                    return $showBtn.' '.$editBtn.' '.$deleteBtn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('proyectos.areas.index');
    }

    public function create()
    {
        $workspaces = Workspace::active()->get();

        // Obtener departamentos desde Softland
        $schema = SchemaHelper::getSchema();
        $departments = DB::connection('softland')
            ->table("{$schema}.DEPARTAMENTO")
            ->select('DEPARTAMENTO as code', 'DESCRIPCION as name')
            ->orderBy('DESCRIPCION')
            ->get();

        return view('proyectos.areas.create', compact('workspaces', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'name' => 'required|string|max:255',
            'departamento_softland' => 'nullable|string|max:10',
            'description' => 'nullable|string',
        ]);

        try {
            ProjectArea::create([
                'workspace_id' => $request->workspace_id,
                'name' => $request->name,
                'departamento_softland' => $request->departamento_softland,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
                'created_by' => auth()->id(),
            ]);

            Alert::success('Éxito', 'Área creada correctamente');
            return redirect()->route('proyectos.areas.index');

        } catch (\Exception $e) {
            Alert::error('Error', 'Error al crear área: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function show($id)
    {
        $area = ProjectArea::with(['workspace', 'folders.lists.tasks'])->findOrFail($id);
        return view('proyectos.areas.show', compact('area'));
    }

    public function edit($id)
    {
        $area = ProjectArea::findOrFail($id);
        $workspaces = Workspace::active()->get();

        // Obtener departamentos desde Softland
        $schema = SchemaHelper::getSchema();
        $departments = DB::connection('softland')
            ->table("{$schema}.DEPARTAMENTO")
            ->select('DEPARTAMENTO as code', 'DESCRIPCION as name')
            ->orderBy('DESCRIPCION')
            ->get();

        return view('proyectos.areas.edit', compact('area', 'workspaces', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'name' => 'required|string|max:255',
            'departamento_softland' => 'nullable|string|max:10',
            'description' => 'nullable|string',
        ]);

        try {
            $area = ProjectArea::findOrFail($id);
            $area->update([
                'workspace_id' => $request->workspace_id,
                'name' => $request->name,
                'departamento_softland' => $request->departamento_softland,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
            ]);

            Alert::success('Éxito', 'Área actualizada correctamente');
            return redirect()->route('proyectos.areas.show', $id);

        } catch (\Exception $e) {
            Alert::error('Error', 'Error al actualizar: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $area = ProjectArea::findOrFail($id);
            $area->delete();

            return response()->json([
                'success' => true,
                'message' => 'Área eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener departamentos desde Softland (AJAX)
     */
    public function getDepartments(Request $request)
    {
        try {
            $schema = SchemaHelper::getSchema();
            $search = $request->input('q', ''); // Término de búsqueda de Select2

            $query = DB::connection('softland')
                ->table("{$schema}.DEPARTAMENTO")
                ->select('DEPARTAMENTO as code', 'DESCRIPCION as name');

            // Si hay término de búsqueda, filtrar por código o nombre
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('DESCRIPCION', 'LIKE', "%{$search}%")
                      ->orWhere('DEPARTAMENTO', 'LIKE', "%{$search}%");
                });
            }

            $departments = $query->orderBy('DESCRIPCION')->get();

            return response()->json([
                'success' => true,
                'departments' => $departments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener departamentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuarios de un departamento específico (AJAX)
     */
    public function getUsersByDepartment(Request $request)
    {
        try {
            $departmentCode = $request->input('department');

            if (!$departmentCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe especificar un departamento'
                ], 400);
            }

            $schema = SchemaHelper::getSchema();

            // Obtener usuarios del departamento desde Softland
            $softlandUsers = DB::connection('softland')
                ->table("{$schema}.usuarios_departamento")
                ->where('DEPARTAMENTO', $departmentCode)
                ->pluck('USUARIO');

            if ($softlandUsers->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'users' => []
                ]);
            }

            // Buscar usuarios en la base de datos local
            $users = \App\Models\User::whereIn('softland_user', $softlandUsers)
                ->select('id', 'first_name', 'last_name', 'email', 'softland_user')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => trim($user->first_name . ' ' . $user->last_name),
                        'email' => $user->email,
                        'softland_user' => $user->softland_user,
                    ];
                });

            return response()->json([
                'success' => true,
                'users' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios del departamento: ' . $e->getMessage()
            ], 500);
        }
    }
}
