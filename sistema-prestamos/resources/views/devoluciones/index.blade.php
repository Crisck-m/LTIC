@extends('layouts.panel')

@section('titulo', 'Devoluciones Pendientes')

@section('contenido')
    <div class="card shadow-sm border-danger">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-danger">
                <i class="fas fa-clock me-2"></i>Equipos Pendientes de Devolución
            </h5>
        </div>

        <div class="card-body">

            @if($pendientes->count() > 0)
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        Hay <strong>{{ $pendientes->total() }}</strong> equipos prestados actualmente.
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Equipo</th>
                            <th>Estudiante Solicitante</th>
                            <th>Practicante que Atendió</th>
                            <th>Tiempo Transcurrido</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendientes as $prestamo)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light p-2 rounded me-3 text-primary">
                                            <i class="fas fa-laptop fa-lg"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $prestamo->equipo->tipo }}</div>
                                            <div class="text-muted small">{{ $prestamo->equipo->marca }} -
                                                {{ $prestamo->equipo->modelo }}</div>
                                            <span
                                                class="badge bg-secondary text-light">{{ $prestamo->equipo->nombre_equipo }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-bold">{{ $prestamo->estudiante->nombre }}
                                        {{ $prestamo->estudiante->apellido }}</div>
                                    <div class="text-muted small">
                                        <i class="fas fa-graduation-cap me-1"></i> {{ $prestamo->estudiante->carrera }}
                                    </div>
                                    @if($prestamo->estudiante->telefono)
                                        <div class="text-success small mt-1">
                                            <i class="fas fa-phone-alt me-1"></i> {{ $prestamo->estudiante->telefono }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div class="fw-bold text-info">
                                        <i class="fas fa-user-tie me-1"></i>
                                        {{ $prestamo->practicante ? $prestamo->practicante->nombre . ' ' . $prestamo->practicante->apellido : 'No registrado' }}
                                    </div>
                                    <div class="text-muted small">
                                        Atendió la salida
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-bold text-dark">
                                        {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->diffForHumans() }}
                                    </div>
                                    <div class="text-muted small">
                                        Salida: {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('h:i A') }}
                                    </div>
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('prestamos.finalizar', $prestamo) }}"
                                        class="btn btn-success text-white shadow-sm">
                                        <i class="fas fa-check-circle me-1"></i> Recibir Equipo
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-success mb-3">
                                        <i class="fas fa-check-double fa-4x"></i>
                                    </div>
                                    <h5 class="text-muted">¡Todo en orden!</h5>
                                    <p class="text-muted small">No hay equipos pendientes de devolución en este momento.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $pendientes->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection