<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\SchemaHelper;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'area_id',      // Nueva relación directa con área
        'list_id',      // Mantener para compatibilidad temporal
        'conjunto_id',
        'name',
        'description',
        'status',
        'priority',
        'start_date',
        'due_date',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'progress',
        'parent_task_id',
        'order',
        'is_archived',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'estimated_hours' => 'integer',
        'actual_hours' => 'integer',
        'progress' => 'integer',
        'is_archived' => 'boolean',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-asignar conjunto_id y created_by al crear
        static::creating(function ($task) {
            if (!$task->conjunto_id) {
                $task->conjunto_id = SchemaHelper::getSchema();
            }
            if (!$task->created_by) {
                $task->created_by = auth()->id();
            }
        });

        // Auto-actualizar updated_by
        static::updating(function ($task) {
            $task->updated_by = auth()->id();
        });

        // Global scope para filtrar por conjunto
        static::addGlobalScope('conjunto', function ($builder) {
            $conjunto = SchemaHelper::getSchema();
            if ($conjunto) {
                $builder->where('conjunto_id', $conjunto);
            }
        });
    }

    /**
     * Relación con ProjectArea (estructura simplificada)
     * Acceso directo: Workspace → Area → Task
     */
    public function area()
    {
        return $this->belongsTo(ProjectArea::class, 'area_id');
    }

    /**
     * Relación con usuarios asignados
     * IMPORTANTE: No usar con ->with() en eager loading
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_assignments', 'task_id', 'user_id')
            ->withPivot('assignment_type', 'assigned_by', 'assigned_at');
    }

    /**
     * Relación con asignaciones de departamento
     */
    public function departmentAssignments()
    {
        return $this->hasMany(TaskDepartmentAssignment::class, 'task_id');
    }

    /**
     * Relación con actividades de la tarea
     */
    public function activities()
    {
        return $this->hasMany(TaskActivity::class, 'task_id')->orderBy('created_at', 'desc');
    }

    /**
     * Relación con registros de tiempo
     */
    public function timeEntries()
    {
        return $this->hasMany(TaskTimeEntry::class, 'task_id')->orderBy('start_time', 'desc');
    }

    /**
     * Relación con etiquetas (tags)
     */
    public function tags()
    {
        return $this->belongsToMany(TaskTag::class, 'task_tag_pivot', 'task_id', 'tag_id')
            ->withTimestamps();
    }

    /**
     * Usuario creador
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que actualizó
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Tarea padre
     */
    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /**
     * Subtareas
     */
    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    /**
     * Dependencias (tareas de las que depende esta)
     */
    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
            ->withTimestamps()
            ->withPivot('dependency_type');
    }

    /**
     * Tareas que dependen de esta
     */
    public function dependents()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id')
            ->withTimestamps()
            ->withPivot('dependency_type');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Accessors
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->due_date || $this->status == 'done') {
            return false;
        }
        return Carbon::parse($this->due_date)->isPast();
    }

    public function getProgressPercentageAttribute()
    {
        return $this->progress . '%';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pendiente',
            'in_progress' => 'En Progreso',
            'training' => 'Capacitación',
            'done' => 'Completada',
        ];
        return $labels[$this->status] ?? 'Desconocido';
    }

    public function getPriorityLabelAttribute()
    {
        $labels = [
            'urgent' => 'Urgente',
            'high' => 'Alta',
            'normal' => 'Normal',
            'low' => 'Baja',
        ];
        return $labels[$this->priority] ?? 'Desconocido';
    }

    /**
     * Obtener el porcentaje de completitud de la tarea
     *
     * @return int
     */
    public function getCompletionPercentage()
    {
        return $this->progress ?? 0;
    }

    /**
     * Obtener el tiempo total registrado en minutos
     *
     * @return int
     */
    public function getTotalTimeSpent()
    {
        return $this->timeEntries()->sum('duration_minutes');
    }

    /**
     * Relación con items del checklist (actividades)
     */
    public function checklistItems()
    {
        return $this->hasMany(TaskChecklistItem::class)->orderBy('order');
    }

    /**
     * Actualizar el progreso automáticamente basado en checklist items completados
     * Si no hay items, mantiene el progreso manual
     */
    public function updateProgress()
    {
        $totalItems = $this->checklistItems()->count();

        if ($totalItems === 0) {
            // No hay items, mantener progreso manual
            return;
        }

        $completedItems = $this->checklistItems()->where('is_completed', true)->count();
        $percentage = ($totalItems > 0) ? round(($completedItems / $totalItems) * 100) : 0;

        $this->progress = $percentage;
        $this->save();

        // Si está 100% completado, marcar como done
        if ($percentage >= 100 && $this->status !== 'done') {
            $this->status = 'done';
            $this->completed_at = now();
            $this->save();
        }
        // Si hay actividades pendientes y la tarea estaba completada, reabrir
        elseif ($percentage < 100 && $this->status === 'done') {
            $this->status = 'in_progress';
            $this->completed_at = null;
            $this->save();
        }
    }

    /**
     * Actualizar las horas reales sumando las de las actividades completadas
     * y las de time entries (si las hay)
     */
    public function updateActualHours()
    {
        // Sumar horas de checklist items completados
        $checklistHours = $this->checklistItems()
            ->where('is_completed', true)
            ->sum('duration_hours');

        // Sumar horas de time entries (en minutos, convertir a horas)
        $timeEntriesMinutes = $this->timeEntries()->sum('duration_minutes');
        $timeEntriesHours = $timeEntriesMinutes / 60;

        // Total de horas reales
        $totalHours = $checklistHours + $timeEntriesHours;

        // Actualizar campo actual_hours (mantener decimales para precisión)
        $this->actual_hours = round($totalHours, 2);
        $this->save();
    }

    /**
     * Obtener el total de horas de checklist items completados
     */
    public function getChecklistHours()
    {
        return $this->checklistItems()
            ->where('is_completed', true)
            ->sum('duration_hours');
    }

    /**
     * Obtener el total de horas (checklist + time entries)
     */
    public function getTotalHours()
    {
        $checklistHours = $this->getChecklistHours();
        $timeEntriesHours = $this->getTotalTimeSpent() / 60;
        return $checklistHours + $timeEntriesHours;
    }
}
