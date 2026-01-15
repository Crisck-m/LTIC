@extends('layouts.panel')

@section('titulo', 'Home')

@section('contenido')
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('estudiantes.index') }}" class="text-decoration-none">
                <div class="card border-left-primary shadow h-100 py-2 border-0 border-start border-4 border-primary">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Estudiantes Registrados</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['total_estudiantes'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300 text-primary opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('equipos.index') }}" class="text-decoration-none">
                <div class="card border-left-info shadow h-100 py-2 border-0 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Equipos Disponibles</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['equipos_disponibles'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-laptop fa-2x text-gray-300 text-info opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('prestamos.index') }}" class="text-decoration-none">
                <div class="card border-left-warning shadow h-100 py-2 border-0 border-start border-4 border-warning">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Préstamos Activos</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['prestamos_activos'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hand-holding fa-2x text-gray-300 text-warning opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('prestamos.index', ['estado' => 'activo']) }}" class="text-decoration-none">
                <div class="card border-left-danger shadow h-100 py-2 border-0 border-start border-4 border-danger">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Pendientes Devolución</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['pendientes_devolucion'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300 text-danger opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt text-warning me-2"></i>Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('estudiantes.create') }}" class="btn btn-primary w-100 h-100 py-3">
                                <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                                Registrar Estudiante
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('equipos.create') }}" class="btn btn-primary w-100 h-100 py-3">
                                <i class="fas fa-laptop fa-2x mb-2"></i><br>
                                Agregar Equipo
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('prestamos.create') }}" class="btn btn-primary w-100 h-100 py-3">
                                <i class="fas fa-hand-holding fa-2x mb-2"></i><br>
                                Nuevo Préstamo
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('devoluciones.index') }}" class="btn btn-primary w-100 h-100 py-3">
                                <i class="fas fa-undo fa-2x mb-2"></i><br>
                                Registrar Devolución
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Actividad Reciente
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($stats['ultimos_movimientos'] as $p)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    @if($p->estado == 'activo')
                                        <i class="fas fa-hand-holding text-warning me-2"></i>
                                        <strong>{{ $p->estudiante->nombre }}</strong> solicitó {{ $p->equipo->tipo }}
                                    @else
                                        <i class="fas fa-undo text-success me-2"></i>
                                        <strong>{{ $p->estudiante->nombre }}</strong> devolvió {{ $p->equipo->tipo }}
                                    @endif
                                </div>
                                <small class="text-muted">{{ $p->updated_at->diffForHumans() }}</small>
                            </div>
                        @endforeach
                        
                        @if($stats['ultimos_movimientos']->isEmpty())
                            <p class="text-center text-muted my-3">No hay actividad reciente.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle me-2"></i>Sistema
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Versión</small>
                        <div class="font-weight-bold">1.0.0</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Fecha</small>
                        <div>{{ now()->format('d/m/Y') }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Estado</small>
                        <div><span class="badge bg-success">Operativo</span></div>
                    </div>
                    <div>
                        <small class="text-muted">Soporte</small>
                        <div><a href="mailto:soporte@ltic.edu">soporte@ltic.edu</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection