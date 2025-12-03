@extends('layouts.panel')

@section('titulo', 'Gestión de Préstamos')

@section('contenido')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-secondary">
            <i class="fas fa-history me-2"></i>Historial de Préstamos
        </h5>
        <a href="{{ route('prestamos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Préstamo
        </a>
    </div>

    <div class="card-body">
        
        <form action="{{ route('prestamos.index') }}" method="GET">
            <div class="row mb-3">
                
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Buscar por estudiante, código o equipo..." 
                               value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="estado" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>En Curso</option>
                        <option value="finalizado" {{ request('estado') == 'finalizado' ? 'selected' : '' }}>Devueltos</option>
                    </select>
                </div>
                
                @if(request('search') || request('estado'))
                    <div class="col-md-2">
                        <a href="{{ route('prestamos.index') }}" class="btn btn-outline-danger">
                            <i class="fas fa-eraser"></i> Limpiar
                        </a>
                    </div>
                @endif

            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Equipo</th>
                        <th>Estudiante (Receptor)</th>
                        <th>Fecha y Atención</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prestamos as $prestamo)
                        <tr>
                            <td>
                                <div class="fw-bold text-primary">
                                    <i class="fas fa-laptop me-1"></i> {{ $prestamo->equipo->tipo }}
                                </div>
                                <div class="small text-muted">
                                    {{ $prestamo->equipo->marca }} - {{ $prestamo->equipo->modelo }} 
                                    <span class="badge bg-light text-dark border">{{ $prestamo->equipo->codigo_puce }}</span>
                                </div>
                            </td>
                            
                            <td>
                                <div class="fw-bold">{{ $prestamo->estudiante->nombre }} {{ $prestamo->estudiante->apellido }}</div>
                                <div class="small text-muted">{{ $prestamo->estudiante->carrera }}</div>
                            </td>

                            <td>
                                <div><i class="far fa-calendar-alt me-1 text-secondary"></i> {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y') }}</div>
                                <div class="small text-muted"><i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('h:i A') }}</div>
                                
                                <div class="small text-info fst-italic mt-1" style="font-size: 0.85em;">
                                    <i class="fas fa-user-tie me-1"></i> Atendió: {{ $prestamo->pasante->nombre ?? 'Admin' }} {{ $prestamo->pasante->apellido ?? '' }}
                                </div>
                            </td>

                            <td class="text-center">
                                @if($prestamo->estado == 'activo')
                                    <span class="badge bg-warning text-dark border border-warning rounded-pill px-3">
                                        <i class="fas fa-spinner fa-spin me-1"></i> En Curso
                                    </span>
                                @elseif($prestamo->estado == 'finalizado')
                                    <span class="badge bg-success rounded-pill px-3">
                                        <i class="fas fa-check me-1"></i> Devuelto
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">{{ $prestamo->estado }}</span>
                                @endif
                            </td>

                            <td class="text-end">
                                @if($prestamo->estado == 'activo')
                                    <a href="{{ route('prestamos.finalizar', $prestamo) }}" class="btn btn-sm btn-success" title="Registrar Devolución">
                                        <i class="fas fa-undo me-1"></i> Devolver
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="fas fa-clipboard-list fa-3x"></i></div>
                                <h6 class="text-muted">No hay historial de préstamos.</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $prestamos->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection