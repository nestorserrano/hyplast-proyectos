@extends('adminlte::page')

@section('title', 'Calendario - Tareas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Calendario de Tareas</h1>
        <div class="btn-group" role="group">
            <a href="{{ route('proyectos.tasks.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-list"></i> Lista
            </a>
            <a href="{{ route('proyectos.tasks.kanban') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-columns"></i> Kanban
            </a>
            <a href="{{ route('proyectos.tasks.calendar') }}" class="btn btn-sm btn-outline-primary active">
                <i class="fas fa-calendar"></i> Calendario
            </a>
            <a href="{{ route('proyectos.tasks.hierarchy') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-sitemap"></i> Jerarquía
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
@stop

@section('css')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
@stop

@section('js')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: {
                    url: '{{ route('proyectos.tasks.calendar.events') }}',
                    method: 'GET',
                    failure: function(error) {
                        console.error('Error al cargar eventos:', error);
                        alert('Error al cargar las tareas. Por favor, revise la consola para más detalles.');
                    }
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                },
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false
                }
            });

            calendar.render();
        });
    </script>
@stop
