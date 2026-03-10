<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'name',
        'description',
        'assigned_to',
        'is_completed',
        'started_at',
        'completed_at',
        'completed_by',
        'duration_hours',
        'order',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_hours' => 'decimal:2',
    ];

    /**
     * Relación con la tarea padre
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Usuario asignado a la actividad
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Usuario que completó la actividad
     */
    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Marcar actividad como completada
     */
    public function markAsCompleted($userId = null)
    {
        $this->is_completed = true;
        $this->completed_at = now();
        $this->completed_by = $userId ?? auth()->id();

        // Calcular duración en horas (con decimales)
        if ($this->started_at) {
            // Usar diffInMinutes y dividir por 60 para obtener horas con decimales
            $minutes = $this->started_at->diffInMinutes($this->completed_at);
            $this->duration_hours = round($minutes / 60, 2);
            \Log::info("Actividad completada - ID: {$this->id}, Started: {$this->started_at}, Completed: {$this->completed_at}, Minutes: {$minutes}, Hours: {$this->duration_hours}");
        } else {
            // Si no tiene started_at, usar created_at
            $minutes = $this->created_at->diffInMinutes($this->completed_at);
            $this->duration_hours = round($minutes / 60, 2);
            \Log::warning("Actividad completada sin started_at - ID: {$this->id}, usando created_at. Minutes: {$minutes}, Hours: {$this->duration_hours}");
        }

        $this->save();

        // Actualizar el progreso y tiempo de la tarea padre
        $this->task->updateProgress();
        $this->task->updateActualHours();
    }

    /**
     * Marcar actividad como no completada
     */
    public function markAsIncomplete()
    {
        $oldDuration = $this->duration_hours;

        $this->is_completed = false;
        $this->completed_at = null;
        $this->completed_by = null;
        $this->duration_hours = null;
        $this->save();

        // Actualizar el progreso y tiempo de la tarea padre
        $this->task->updateProgress();
        $this->task->updateActualHours();
    }

    /**
     * Iniciar actividad (marcar fecha de inicio)
     */
    public function start()
    {
        if (!$this->started_at) {
            $this->started_at = now();
            $this->save();
        }
    }

    /**
     * Obtener duración formateada
     */
    public function getFormattedDuration()
    {
        // Si no hay duración o es 0, retornar mensaje por defecto
        if (!$this->duration_hours || $this->duration_hours <= 0) {
            return '-';
        }

        $hours = floor($this->duration_hours);
        $minutes = round(($this->duration_hours - $hours) * 60);

        if ($hours > 24) {
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;
            return "{$days} día(s) {$remainingHours} hora(s) {$minutes} min";
        }

        return "{$hours} hora(s) {$minutes} min";
    }

    /**
     * Verificar si está en progreso (iniciada pero no completada)
     */
    public function isInProgress()
    {
        return $this->started_at && !$this->is_completed;
    }
}
