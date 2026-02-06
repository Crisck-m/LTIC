@extends('layouts.panel')

@section('titulo', 'Confirmar Devolución')

@section('contenido')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning bg-opacity-10 py-3">
                        <h5 class="mb-0 text-dark">
                            <i class="fas fa-exclamation-triangle me-2"></i>Verificar Devolución de Equipo(s)
                        </h5>
                    </div>

                    {{-- Mostrar errores de validación --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show m-3 mb-0" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div>
                                    <strong>Error al procesar la devolución:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card-body p-4">
                        <form action="{{ route('prestamos.devolver', $prestamo) }}" method="POST" id="formDevolucion">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <div class="col-12">
                                    <h6 class="text-secondary border-bottom pb-2 mb-3">Datos del Préstamo Activo</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Estudiante Solicitante</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $prestamo->estudiante->nombre }} {{ $prestamo->estudiante->apellido }}"
                                        readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fecha de Salida</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y h:i A') }}"
                                        readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Practicante que Atendió</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $prestamo->practicante ? $prestamo->practicante->nombre . ' ' . $prestamo->practicante->apellido : 'No registrado' }}"
                                        readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Observaciones Iniciales</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $prestamo->observaciones_prestamo ?? 'Ninguna' }}" readonly>
                                </div>

                                <!-- SELECCIÓN DE EQUIPOS A DEVOLVER -->
                                <div class="col-12 mt-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="fas fa-check-square me-2"></i>Seleccionar Equipos a Recibir
                                    </h6>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Importante:</strong> Marca solo los equipos que el estudiante está devolviendo en este momento.
                                    </div>

                                    @php
                                        $equiposActivos = $prestamo->prestamoEquipos()->where('estado', 'activo')->with('equipo')->get();
                                    @endphp

                                    @if($equiposActivos->count() > 0)
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="seleccionarTodos" 
                                                    {{ $equiposActivos->count() == 1 ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="seleccionarTodos">
                                                    Seleccionar todos los equipos ({{ $equiposActivos->count() }})
                                                </label>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            @foreach($equiposActivos as $pe)
                                                <div class="col-md-6">
                                                    <div class="card border-2 equipo-card" data-equipo-id="{{ $pe->equipo_id }}">
                                                        <div class="card-body">
                                                            <div class="form-check">
                                                                <input class="form-check-input equipo-checkbox" 
                                                                    type="checkbox" 
                                                                    name="equipos_devolver[]" 
                                                                    value="{{ $pe->equipo_id }}" 
                                                                    id="equipo_{{ $pe->equipo_id }}"
                                                                    {{ $equiposActivos->count() == 1 ? 'checked' : '' }}>
                                                                <label class="form-check-label w-100" for="equipo_{{ $pe->equipo_id }}">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="bg-light p-2 rounded me-3">
                                                                            <i class="fas fa-laptop fa-2x text-primary"></i>
                                                                        </div>
                                                                        <div>
                                                                            <div class="fw-bold">{{ $pe->equipo->tipo }}</div>
                                                                            <div class="text-muted small">
                                                                                {{ $pe->equipo->marca }} - {{ $pe->equipo->modelo }}
                                                                            </div>
                                                                            <span class="badge bg-secondary">{{ $pe->equipo->nombre_equipo }}</span>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="mt-3">
                                            <div class="alert alert-warning d-none" id="alertaSeleccion">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <strong>Atención:</strong> No has seleccionado ningún equipo para devolver.
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            No hay equipos activos pendientes de devolución.
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12 mt-4">
                                    <h6 class="text-success border-bottom pb-2 mb-3">Registro de Entrada (Devolución)</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Practicante que recibe <span
                                            class="text-danger">*</span></label>
                                    <select name="practicante_recibe_id" class="form-select" required>
                                        <option value="" disabled selected>Seleccione quién recibe...</option>
                                        @foreach($practicantes as $practicante)
                                            <option value="{{ $practicante->id }}">
                                                {{ $practicante->nombre }} {{ $practicante->apellido }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted small">Responsable de verificar la devolución.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fecha y Hora de Confirmación</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ \Carbon\Carbon::now()->format('d/m/Y h:i A') }}" readonly>
                                    <div class="form-text text-muted small">Fecha y hora de recepción del equipo.</div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Observaciones de Recepción (Estado del equipo)</label>
                                    <textarea name="observaciones_devolucion" rows="3" class="form-control"
                                        placeholder="Ej: Equipos devueltos en buenas condiciones, sin novedades..."></textarea>
                                </div>

                                <div class="col-md-12">
                                    <div class="alert alert-info mt-2">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Los equipos seleccionados cambiarán automáticamente a estado
                                        <strong>"Disponible"</strong> en el inventario.
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('prestamos.index') }}" class="btn btn-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>

                                <button type="submit" class="btn btn-success px-4" id="btnConfirmar">
                                    <i class="fas fa-check-circle me-2"></i>Confirmar Devolución
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .equipo-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .equipo-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: #0d6efd !important;
        }

        .equipo-checkbox:checked ~ label {
            font-weight: bold;
        }

        .equipo-card:has(.equipo-checkbox:checked) {
            background-color: #e7f3ff;
            border-color: #0d6efd !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seleccionarTodos = document.getElementById('seleccionarTodos');
            const checkboxes = document.querySelectorAll('.equipo-checkbox');
            const alertaSeleccion = document.getElementById('alertaSeleccion');
            const btnConfirmar = document.getElementById('btnConfirmar');
            const form = document.getElementById('formDevolucion');

            // Seleccionar/Deseleccionar todos
            if (seleccionarTodos) {
                seleccionarTodos.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    verificarSeleccion();
                });
            }

            // Actualizar "Seleccionar todos" según checkboxes individuales
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const todosSeleccionados = Array.from(checkboxes).every(cb => cb.checked);
                    const algunoSeleccionado = Array.from(checkboxes).some(cb => cb.checked);
                    
                    if (seleccionarTodos) {
                        seleccionarTodos.checked = todosSeleccionados;
                        seleccionarTodos.indeterminate = algunoSeleccionado && !todosSeleccionados;
                    }
                    
                    verificarSeleccion();
                });
            });

            // Verificar que al menos 1 equipo esté seleccionado
            function verificarSeleccion() {
                const algunoSeleccionado = Array.from(checkboxes).some(cb => cb.checked);
                
                if (algunoSeleccionado) {
                    alertaSeleccion.classList.add('d-none');
                    btnConfirmar.disabled = false;
                } else {
                    alertaSeleccion.classList.remove('d-none');
                    btnConfirmar.disabled = true;
                }
            }

            // Validación al enviar formulario
            form.addEventListener('submit', function(e) {
                const algunoSeleccionado = Array.from(checkboxes).some(cb => cb.checked);
                
                if (!algunoSeleccionado) {
                    e.preventDefault();
                    alert('Debes seleccionar al menos un equipo para devolver.');
                    return false;
                }
            });

            // Click en la tarjeta también marca el checkbox
            document.querySelectorAll('.equipo-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (e.target.type !== 'checkbox') {
                        const checkbox = this.querySelector('.equipo-checkbox');
                        if (checkbox) {
                            checkbox.checked = !checkbox.checked;
                            checkbox.dispatchEvent(new Event('change'));
                        }
                    }
                });
            });

            // Verificación inicial
            verificarSeleccion();
        });
    </script>
@endsection