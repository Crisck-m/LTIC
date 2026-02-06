@extends('layouts.panel')

@section('titulo', 'Home')

@section('contenido')
    <div class="container-fluid">

        {{-- ESTADÍSTICAS EN CARDS --}}
        <div class="row g-4 mb-4">

            {{-- EQUIPOS DISPONIBLES --}}
            <div class="col-md-3">
                <a href="{{ route('equipos.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 border-start border-info border-4 card-clickable">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                        <i class="fas fa-laptop fa-2x text-info"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted text-uppercase mb-1 small">Equipos Disponibles</h6>
                                    <h2 class="mb-0 fw-bold text-info">{{ $stats['equipos_disponibles'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- HISTORIAL PRÉSTAMOS (HOY) --}}
            <div class="col-md-3">
                <a href="{{ route('prestamos.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 border-start border-secondary border-4 card-clickable">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3">
                                        <i class="fas fa-history fa-2x text-secondary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted text-uppercase mb-1 small">Historial Préstamos (Hoy)</h6>
                                    <h2 class="mb-0 fw-bold text-secondary">{{ $stats['historial_prestamos'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- DEVOLUCIONES PENDIENTES --}}
            <div class="col-md-3">
                <a href="{{ route('devoluciones.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 border-start border-warning border-4 card-clickable">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                        <i class="fas fa-hand-holding fa-2x text-warning"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted text-uppercase mb-1 small">Devoluciones Pendientes</h6>
                                    <h2 class="mb-0 fw-bold text-warning">{{ $stats['pendientes_devolucion'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- PRÉSTAMOS ATRASADOS (NUEVO) --}}
            <div class="col-md-3">
                <a href="{{ route('prestamos.index', ['estado' => 'atrasado']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 border-start border-danger border-4 card-clickable">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                                        <i class="fas fa-clock fa-2x text-danger"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted text-uppercase mb-1 small">Préstamos Atrasados</h6>
                                    <h2 class="mb-0 fw-bold text-danger">{{ $stats['prestamos_atrasados'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <style>
            .card-clickable {
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .card-clickable:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
            }
        </style>

        {{-- ACCIONES RÁPIDAS --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-bolt text-warning me-2"></i>Acciones Rápidas
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="{{ route('estudiantes.create') }}"
                                    class="btn btn-primary w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                                    <span>Registrar Estudiante</span>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('equipos.create') }}"
                                    class="btn btn-primary w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-laptop fa-2x mb-2"></i>
                                    <span>Agregar Equipo</span>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('prestamos.create') }}"
                                    class="btn btn-primary w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-hand-holding fa-2x mb-2"></i>
                                    <span>Nuevo Préstamo</span>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('devoluciones.index') }}"
                                    class="btn btn-primary w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-undo fa-2x mb-2"></i>
                                    <span>Registrar Devolución</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTIVIDAD RECIENTE Y SISTEMA --}}
        <div class="row g-4">

            {{-- ACTIVIDAD RECIENTE --}}
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-clock text-primary me-2"></i>Actividad Reciente
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($stats['ultimos_movimientos'] as $p)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($p->estado == 'activo')
                                            <i class="fas fa-hand-holding text-warning me-2"></i>
                                            <strong>{{ $p->estudiante->nombre }} {{ $p->estudiante->apellido }}</strong> solicitó
                                            @php
                                                $equiposActivos = $p->prestamoEquipos->where('estado', 'activo');
                                                $primerEquipo = $equiposActivos->first();
                                            @endphp
                                            @if($primerEquipo && $primerEquipo->equipo)
                                                {{ $primerEquipo->equipo->nombre_equipo }}
                                                @if($equiposActivos->count() > 1)
                                                    <small class="text-muted">(+{{ $equiposActivos->count() - 1 }} más)</small>
                                                @endif
                                            @else
                                                <span class="text-muted">equipo(s)</span>
                                            @endif
                                        @else
                                            <i class="fas fa-undo text-success me-2"></i>
                                            <strong>{{ $p->estudiante->nombre }} {{ $p->estudiante->apellido }}</strong> devolvió
                                            @php
                                                $todosEquipos = $p->prestamoEquipos;
                                                $primerEquipo = $todosEquipos->first();
                                            @endphp
                                            @if($primerEquipo && $primerEquipo->equipo)
                                                {{ $primerEquipo->equipo->nombre_equipo }}
                                                @if($todosEquipos->count() > 1)
                                                    <small class="text-muted">(+{{ $todosEquipos->count() - 1 }} más)</small>
                                                @endif
                                            @else
                                                <span class="text-muted">equipo(s)</span>
                                            @endif
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $p->updated_at->diffForHumans() }}</small>
                                </div>
                            @endforeach

                            @if($stats['ultimos_movimientos']->isEmpty())
                                <div class="list-group-item text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    <p class="mb-0">No hay actividad reciente</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- INFORMACIÓN DEL SISTEMA --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle text-info me-2"></i>Sesión Actual
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <strong class="d-block text-muted small">Fecha</strong>
                                <span class="fs-5"><i
                                        class="far fa-calendar text-info me-2"></i>{{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
                            </li>
                            <li>
                                <strong class="d-block text-muted small">Hora</strong>
                                <span class="fs-5"><i
                                        class="far fa-clock text-success me-2"></i>{{ \Carbon\Carbon::now()->format('h:i A') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
@endsection