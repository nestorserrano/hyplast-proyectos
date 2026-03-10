<div class="card mb-2 kanban-card" data-task-id="{{ $task->id }}">
    <div class="card-body p-2">
        <h6 class="mb-1">
            <a href="{{ route('proyectos.tasks.show', $task->id) }}" class="text-dark">
                {{ $task->name }}
            </a>
        </h6>

        <small class="text-muted d-block mb-1">
            <i class="fas fa-list"></i> {{ $task->list->name ?? '-' }}
        </small>

        @if($task->priority == 'urgent')
            <span class="badge badge-danger">Urgente</span>
        @elseif($task->priority == 'high')
            <span class="badge badge-warning">Alta</span>
        @endif

        @if($task->due_date)
            <small class="d-block {{ $task->is_overdue ? 'text-danger font-weight-bold' : 'text-muted' }}">
                <i class="fas fa-calendar"></i> {{ $task->due_date->format('d/m/Y') }}
            </small>
        @endif

        @if($task->assignedUsers->count() > 0)
            <div class="mt-1">
                @foreach($task->assignedUsers->take(3) as $user)
                    <span class="badge badge-secondary badge-sm">{{ $user->name }}</span>
                @endforeach
                @if($task->assignedUsers->count() > 3)
                    <span class="badge badge-light">+{{ $task->assignedUsers->count() - 3 }}</span>
                @endif
            </div>
        @endif

        @if($task->tags->count() > 0)
            <div class="mt-1">
                @foreach($task->tags->take(2) as $tag)
                    <span class="badge badge-sm" style="background-color: {{ $tag->color }}; color: white;">
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>
