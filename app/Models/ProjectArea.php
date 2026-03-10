<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\SchemaHelper;
use Illuminate\Support\Facades\DB;

class ProjectArea extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_areas';

    protected $fillable = [
        'workspace_id',
        'conjunto_id',
        'departamento_softland',
        'name',
        'description',
        'color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-asignar conjunto_id al crear
        static::creating(function ($area) {
            if (!$area->conjunto_id) {
                $area->conjunto_id = SchemaHelper::getSchema();
            }
        });

        // Global scope para filtrar por conjunto del usuario
        static::addGlobalScope('conjunto', function ($builder) {
            $conjunto = SchemaHelper::getSchema();
            if ($conjunto) {
                $builder->where('conjunto_id', $conjunto);
            }
        });
    }

    /**
     * Relación con Workspace
     * IMPORTANTE: No usar con ->with() en eager loading
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Relación con Tareas (estructura simplificada)
     * Acceso directo: Area → Task
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'area_id');
    }

    /**
     * Accessor para información del departamento Softland
     * Consulta directa a Softland
     */
    public function getDepartmentInfoAttribute()
    {
        if (!$this->departamento_softland) {
            return null;
        }

        $schema = SchemaHelper::getSchema();

        try {
            return DB::connection('softland')
                ->table("{$schema}.DEPARTAMENTO")
                ->where('DEPARTAMENTO', $this->departamento_softland)
                ->select('DEPARTAMENTO', 'DESCRIPCION')
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Scope para áreas activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
