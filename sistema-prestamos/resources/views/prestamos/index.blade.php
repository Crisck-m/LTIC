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
                                placeholder="Buscar por estudiante, código o equipo..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <select name="estado" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>En Curso</option>
                            <option value="finalizado" {{ request('estado') == 'finalizado' ? 'selected' : '' }}>Devueltos
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
                            value="{{ request('fecha_desde') }}" placeholder="Desde" title="Fecha desde"
                            onchange="validarFechas()">
                        <small class="text-muted">Desde</small>
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
                            value="{{ request('fecha_hasta') }}" placeholder="Hasta" title="Fecha hasta"
                            onchange="validarFechas()">
                        <small class="text-muted">Hasta</small>
                    </div>

                    @if(request('search') || request('estado') || request('fecha_desde') || request('fecha_hasta'))
                        <div class="col-md-2">
                            <a href="{{ route('prestamos.index') }}" class="btn btn-outline-danger w-100">
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
                            <th>Estudiante Solicitante</th>
                            <th>Estudiante Receptor</th>
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
                                        <span
                                            class="badge bg-light text-dark border">{{ $prestamo->equipo->nombre_equipo }}</span>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-bold">{{ $prestamo->estudiante->nombre }}
                                        {{ $prestamo->estudiante->apellido }}
                                    </div>
                                    <div class="small text-muted">{{ $prestamo->estudiante->carrera }}</div>
                                </td>

                                <td>
                                    @if($prestamo->practicanteDevolucion)
                                        <div class="fw-bold text-success">
                                            <i class="fas fa-user-check me-1"></i>
                                            {{ $prestamo->practicanteDevolucion->nombre }}
                                            {{ $prestamo->practicanteDevolucion->apellido }}
                                        </div>
                                        <div class="small text-muted">Practicante receptor</div>
                                    @else
                                        <div class="text-muted fst-italic">
                                            <i class="fas fa-hourglass-half me-1"></i> Pendiente
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div><i class="far fa-calendar-alt me-1 text-secondary"></i>
                                        {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y') }}</div>
                                    <div class="small text-muted"><i class="far fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('h:i A') }}</div>

                                    <div class="small text-info fst-italic mt-1" style="font-size: 0.85em;">
                                        <i class="fas fa-user-tie me-1"></i> Atendió:
                                        {{ $prestamo->practicante->nombre ?? 'Admin' }}
                                        {{ $prestamo->practicante->apellido ?? '' }}
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
                                        <a href="{{ route('prestamos.finalizar', $prestamo) }}" class="btn btn-sm btn-success"
                                            title="Registrar Devolución">
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
                                <td colspan="6" class="text-center py-5">
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

    @push('scripts')
        <script>
            function validarFechas() {
                const fechaDesde = document.getElementById('fecha_desde');
                const fechaHasta = document.getElementById('fecha_hasta');

                // Actualizar el atributo min de fecha_hasta cuando se selecciona fecha_desde
                if (fechaDesde.value) {
                    fechaHasta.min = fechaDesde.value;
                } else {
                    fechaHasta.removeAttribute('min');
                }

                // Validar que fecha_hasta no sea menor que fecha_desde
                if (fechaDesde.value && fechaHasta.value) {
                    if (fechaHasta.value < fechaDesde.value) {
                        alert('La fecha "Hasta" no puede ser anterior a la fecha "Desde".');
                        fechaHasta.value = '';
                        return false;
                    }
                }

                // Si la validación pasa, enviar el formulario
                if (fechaDesde.value || fechaHasta.value) {
                    fechaDesde.form.submit();
                }
            }

            // Establecer el min al cargar la página si ya hay un valor en fecha_desde
            document.addEventListener('DOMContentLoaded', function () {
                const fechaDesde = document.getElementById('fecha_desde');
                const fechaHasta = document.getElementById('fecha_hasta');

                if (fechaDesde.value) {
                    fechaHasta.min = fechaDesde.value;
                }
            });
        </script>
    @endpush
@endsection