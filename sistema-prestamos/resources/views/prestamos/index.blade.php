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
                            <option value="atrasado" {{ request('estado') == 'atrasado' ? 'selected' : '' }}
                                class="text-danger">Atrasados</option>
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
                            <th>Equipo(s)</th>
                            <th>Estudiante Solicitante</th>
                            <th>Practicante Receptor</th>
                            <th>Fecha y Atención</th>
                            <th>Fecha Devolución</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($prestamos as $prestamo)
                            <tr>
                                <!-- EQUIPOS -->
                                <td>
                                    @php
                                        $equiposActivos = $prestamo->prestamoEquipos()->where('estado', 'activo')->with('equipo')->get();
                                        $totalEquipos = $prestamo->prestamoEquipos()->where('estado', 'activo')->count();
                                        $equiposDevueltos = $prestamo->prestamoEquipos()->where('estado', 'devuelto')->count();

                                        // ===================================================================
                                        // AGRUPAR EQUIPOS POR ID (para contar duplicados)
                                        // ===================================================================
                                        $equiposAgrupados = $equiposActivos->groupBy('equipo_id')->map(function ($grupo) {
                                            return [
                                                'equipo' => $grupo->first()->equipo,
                                                'cantidad' => $grupo->count()
                                            ];
                                        });
                                    @endphp

                                    @if($equiposAgrupados->count() > 0)
                                        @foreach($equiposAgrupados->take(2) as $item)
                                            <div class="fw-bold text-primary">
                                                <i class="fas fa-laptop me-1"></i> {{ $item['equipo']->tipo }}
                                                @if($item['cantidad'] > 1)
                                                    <span class="badge bg-primary rounded-pill ms-1">x{{ $item['cantidad'] }}</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted">
                                                {{ $item['equipo']->marca }} - {{ $item['equipo']->modelo }}
                                                <span
                                                    class="badge bg-light text-dark border">{{ $item['equipo']->nombre_equipo }}</span>
                                            </div>
                                        @endforeach

                                        @if($equiposAgrupados->count() > 2)
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-plus-circle me-1"></i> +{{ $equiposAgrupados->count() - 2 }} tipo(s)
                                                más
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-success">
                                            <i class="fas fa-check-circle me-1"></i> Todos devueltos
                                        </div>
                                    @endif

                                    @if($totalEquipos > 1)
                                        <div class="small text-info mt-2">
                                            <i class="fas fa-info-circle me-1"></i> Total: {{ $totalEquipos }} equipo(s)
                                            @if($equiposDevueltos > 0)
                                                ({{ $equiposDevueltos }} devuelto(s))
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <!-- ESTUDIANTE SOLICITANTE -->
                                <td>
                                    <div class="fw-bold">{{ $prestamo->estudiante->nombre }}
                                        {{ $prestamo->estudiante->apellido }}
                                    </div>
                                    <div class="small text-muted">{{ $prestamo->estudiante->carrera }}</div>
                                </td>

                                <!-- PRACTICANTE RECEPTOR (NUEVO) -->
                                <td>
                                    @php
                                        $ultimaDevolucion = $prestamo->prestamoEquipos()
                                            ->where('estado', 'devuelto')
                                            ->whereNotNull('practicante_recibe_id')
                                            ->orderBy('fecha_devolucion_real', 'desc')
                                            ->with('practicanteRecibe')
                                            ->first();
                                    @endphp

                                    @if($ultimaDevolucion && $ultimaDevolucion->practicanteRecibe)
                                        <div class="text-success">
                                            <i class="fas fa-user-check me-1"></i>
                                            <span class="fw-bold">
                                                {{ $ultimaDevolucion->practicanteRecibe->nombre }}
                                                {{ $ultimaDevolucion->practicanteRecibe->apellido }}
                                            </span>
                                        </div>
                                        @if($equiposDevueltos > 1)
                                            <small class="text-muted fst-italic">(última devolución)</small>
                                        @endif
                                    @else
                                        <div class="text-warning">
                                            <i class="fas fa-hourglass-half me-1"></i>
                                            <span class="fw-bold">Pendiente</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- FECHA Y ATENCIÓN -->
                                <td>
                                    <div><i class="far fa-calendar-alt me-1 text-secondary"></i>
                                        {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y') }}</div>
                                    <div class="small text-muted"><i class="far fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('h:i A') }}</div>

                                    <div class="small text-info fst-italic mt-1" style="font-size: 0.85em;">
                                        <i class="fas fa-user-tie me-1"></i> Entregó:
                                        {{ $prestamo->practicante->nombre ?? 'Admin' }}
                                        {{ $prestamo->practicante->apellido ?? '' }}
                                    </div>
                                </td>

                                <!-- FECHA DEVOLUCIÓN (NUEVO) -->
                                <td>
                                    @if($ultimaDevolucion && $ultimaDevolucion->fecha_devolucion_real)
                                        <div class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            <span class="fw-bold">
                                                {{ \Carbon\Carbon::parse($ultimaDevolucion->fecha_devolucion_real)->format('d/m/Y') }}
                                            </span>
                                        </div>
                                        <div class="small text-muted">
                                            {{ \Carbon\Carbon::parse($ultimaDevolucion->fecha_devolucion_real)->format('h:i A') }}
                                        </div>
                                        @if($equiposDevueltos > 1)
                                            <small class="text-muted fst-italic">(última)</small>
                                        @endif
                                    @else
                                        <div class="text-warning">
                                            <i class="fas fa-hourglass-half me-1"></i>
                                            <span class="fw-bold">Pendiente</span>
                                        </div>
                                        <div class="small text-muted">
                                            Esperada:
                                            {{ \Carbon\Carbon::parse($prestamo->fecha_devolucion_esperada)->format('d/m/Y') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- ESTADO -->
                                <!-- ESTADO -->
                                <td class="text-center">
                                    @php
                                        $equiposActivosCount = $prestamo->prestamoEquipos()->where('estado', 'activo')->count();
                                        $equiposDevueltosCount = $prestamo->prestamoEquipos()->where('estado', 'devuelto')->count();
                                        $totalEquipos = $prestamo->prestamoEquipos()->whereIn('estado', ['activo', 'devuelto'])->count();

                                        // Verificar si está atrasado (préstamos activos) - SOLO COMPARAR FECHAS
                                        $fechaEsperada = \Carbon\Carbon::parse($prestamo->fecha_devolucion_esperada)->startOfDay();
                                        $hoy = \Carbon\Carbon::now()->startOfDay();
                                        $estaAtrasado = $prestamo->estado == 'activo' && $hoy->greaterThan($fechaEsperada);

                                        // Verificar si fue devuelto tarde (préstamos finalizados) - SOLO COMPARAR FECHAS
                                        $fueDevueltoTarde = false;
                                        if ($prestamo->estado == 'finalizado') {
                                            $ultimaDevolucion = $prestamo->prestamoEquipos()
                                                ->where('estado', 'devuelto')
                                                ->whereNotNull('fecha_devolucion_real')
                                                ->orderBy('fecha_devolucion_real', 'desc')
                                                ->first();

                                            if ($ultimaDevolucion && $ultimaDevolucion->fecha_devolucion_real) {
                                                $fechaDevolucionReal = \Carbon\Carbon::parse($ultimaDevolucion->fecha_devolucion_real)->startOfDay();
                                                $fechaEsperadaFin = \Carbon\Carbon::parse($prestamo->fecha_devolucion_esperada)->startOfDay();
                                                $fueDevueltoTarde = $fechaDevolucionReal->greaterThan($fechaEsperadaFin);
                                            }
                                        }
                                    @endphp

                                    @if($estaAtrasado)
                                        {{-- ATRASADO (préstamos activos) --}}
                                        <span class="badge bg-danger rounded-pill px-3 py-2 pulse-animation">
                                            <i class="fas fa-exclamation-triangle me-1"></i> ATRASADO
                                            <br>
                                            <small>({{ $equiposActivosCount }}/{{ $totalEquipos }} pendientes)</small>
                                        </span>
                                    @elseif($prestamo->estado == 'activo')
                                        @if($equiposDevueltosCount > 0)
                                            {{-- Devolución parcial --}}
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                                <i class="fas fa-hourglass-half me-1"></i> Parcial
                                                <br>
                                                <small>({{ $equiposDevueltosCount }}/{{ $totalEquipos }})</small>
                                            </span>
                                        @else
                                            {{-- En curso (ninguno devuelto) --}}
                                            <span class="badge bg-info text-white rounded-pill px-3 py-2">
                                                <i class="fas fa-spinner fa-pulse me-1"></i> En Curso
                                                <br>
                                                <small>(0/{{ $totalEquipos }})</small>
                                            </span>
                                        @endif
                                    @elseif($prestamo->estado == 'finalizado')
                                        {{-- Todos devueltos --}}
                                        @if($fueDevueltoTarde)
                                            {{-- DEVUELTO TARDE (sin animación) --}}
                                            <span class="badge bg-success rounded-pill px-3 py-2 position-relative">
                                                <i class="fas fa-check me-1"></i> Completo
                                                <br>
                                                <small>({{ $totalEquipos }}/{{ $totalEquipos }})</small>
                                                <br>
                                                <span class="badge bg-warning text-dark mt-1 small">
                                                    <i class="fas fa-clock"></i> Atrasado
                                                </span>
                                            </span>
                                        @else
                                            {{-- DEVUELTO A TIEMPO --}}
                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                <i class="fas fa-check me-1"></i> Completo
                                                <br>
                                                <small>({{ $totalEquipos }}/{{ $totalEquipos }})</small>
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3">{{ $prestamo->estado }}</span>
                                    @endif
                                </td>
                                <style>
                                    @keyframes pulse {

                                        0%,
                                        100% {
                                            transform: scale(1);
                                        }

                                        50% {
                                            transform: scale(1.05);
                                        }
                                    }

                                    .pulse-animation {
                                        animation: pulse 2s infinite;
                                    }
                                </style>

                                <!-- ACCIONES -->
                                <td class="text-end">
                                    @if($prestamo->estado == 'activo')
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('prestamos.edit', $prestamo) }}"
                                                class="btn btn-sm btn-warning text-dark" title="Editar Préstamo">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('prestamos.finalizar', $prestamo) }}" class="btn btn-sm btn-success"
                                                title="Registrar Devolución">
                                                <i class="fas fa-undo me-1"></i> Devolver
                                            </a>
                                        </div>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
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

                if (fechaDesde.value) {
                    fechaHasta.min = fechaDesde.value;
                } else {
                    fechaHasta.removeAttribute('min');
                }

                if (fechaDesde.value && fechaHasta.value) {
                    if (fechaHasta.value < fechaDesde.value) {
                        alert('La fecha "Hasta" no puede ser anterior a la fecha "Desde".');
                        fechaHasta.value = '';
                        return false;
                    }
                }

                if (fechaDesde.value || fechaHasta.value) {
                    fechaDesde.form.submit();
                }
            }

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