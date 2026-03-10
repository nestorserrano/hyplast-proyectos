# Hyplast Proyectos - Sistema de Gestión de Proyectos

## Descripción
Sistema completo para gestión de proyectos, tareas, seguimiento de avances, colaboración en equipo y análisis de rendimiento.

## Características Principales
- 📋 Gestión de proyectos con áreas y departamentos
- ✅ Sistema de tareas con checklist
- ⏱️ Registro de tiempo trabajado
- 👥 Asignación de tareas por departamento
- 🏷️ Etiquetas y categorización
- 📊 Dashboard de métricas y KPIs
- 📝 Actividades y timeline
- 🔔 Notificaciones automáticas

## Estructura del Proyecto
```
hyplast-proyectos/
├── app/
│   ├── Http/Controllers/
│   │   ├── ProjectAreaController.php
│   │   ├── TaskController.php
│   │   └── TaskChecklistItemController.php
│   ├── Models/
│   │   ├── ProjectArea.php
│   │   ├── Task.php
│   │   ├── TaskActivity.php
│   │   ├── TaskChecklistItem.php
│   │   ├── TaskTag.php
│   │   ├── TaskTimeEntry.php
│   │   └── TaskDepartmentAssignment.php
│   └── Services/
│       └── ProjectService.php
├── database/migrations/
├── resources/views/
└── routes/
```

## Modelos Principales
- **ProjectArea**: Áreas de proyectos
- **Task**: Tareas del proyecto
- **TaskActivity**: Registro de actividades
- **TaskChecklistItem**: Items de checklist
- **TaskTimeEntry**: Entradas de tiempo
- **TaskTag**: Etiquetas de tareas
- **TaskDepartmentAssignment**: Asignaciones por departamento

## API Endpoints
```
GET    /api/projects              # Listar proyectos
POST   /api/projects              # Crear proyecto
GET    /api/projects/{id}         # Ver proyecto
PUT    /api/projects/{id}         # Actualizar proyecto
GET    /api/tasks                 # Listar tareas
POST   /api/tasks                 # Crear tarea
PUT    /api/tasks/{id}            # Actualizar tarea
```

## Requisitos
- PHP >= 8.1
- Laravel >= 10.x
- MySQL/MariaDB

## Instalación
```bash
composer install
php artisan migrate
php artisan db:seed --class=ProjectSeeder
```

## Autor y Propietario
**Néstor Serrano**  
Desarrollador Full Stack  
GitHub: [@nestorserrano](https://github.com/nestorserrano)

## Licencia
Propietario - © 2026 Néstor Serrano. Todos los derechos reservados.
