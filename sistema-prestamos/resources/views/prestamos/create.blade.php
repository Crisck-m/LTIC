@extends('layouts.panel')

@section('titulo', 'Nuevo Préstamo')

@section('contenido')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-secondary">
                                <i class="fas fa-hand-holding me-2"></i>Registrar Salida de Equipo
                            </h5>
                            <a href="{{ route('estudiantes.create') }}" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i>Registrar estudiante
                            </a>
                        </div>
                    </div>

                    {{-- Mostrar errores de validación --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show m-3 mb-0" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div>
                                    <strong>Error al procesar el préstamo:</strong>
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
                        <form action="{{ route('prestamos.store') }}" method="POST" id="formPrestamo">
                            @csrf

                            <div class="row g-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Datos del Préstamo</h6>
                                </div>

                                <!-- BÚSQUEDA DE ESTUDIANTE -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Estudiante Solicitante <span
                                            class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i
                                                    class="fas fa-user-graduate"></i></span>
                                            <input type="text" id="buscarEstudiante" class="form-control"
                                                placeholder="Buscar por cédula o nombre..." autocomplete="off">
                                        </div>
                                        <input type="hidden" name="estudiante_id" id="estudiante_id" required>

                                        <!-- Resultados de búsqueda -->
                                        <div id="resultadosEstudiante" class="search-results"></div>

                                        <!-- Estudiante seleccionado -->
                                        <div id="estudianteSeleccionado" class="selected-item mt-2" style="display: none;">
                                            <div
                                                class="alert alert-success mb-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong id="estudianteNombre"></strong><br>
                                                    <small id="estudianteDetalle"></small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="limpiarEstudiante()">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- BÚSQUEDA DE EQUIPO -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Equipo a Entregar <span
                                            class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-laptop"></i></span>
                                            <input type="text" id="buscarEquipo" class="form-control"
                                                placeholder="Buscar por código, serie, tipo o marca..." autocomplete="off">
                                        </div>
                                        <input type="hidden" name="equipo_id" id="equipo_id" required>

                                        <!-- Resultados de búsqueda -->
                                        <div id="resultadosEquipo" class="search-results"></div>

                                        <!-- Equipo seleccionado -->
                                        <div id="equipoSeleccionado" class="selected-item mt-2" style="display: none;">
                                            <div
                                                class="alert alert-info mb-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong id="equipoNombre"></strong><br>
                                                    <small id="equipoDetalle"></small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="limpiarEquipo()">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-text text-success mt-1">
                                        <i class="fas fa-check-circle"></i> Solo se muestran equipos disponibles.
                                    </div>
                                </div>

                                <div class="col-12 mt-3">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Detalles y Responsables</h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-info">Atendido por (Practicante) <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-info bg-opacity-10 text-dark"><i
                                                class="fas fa-user-tie"></i></span>
                                        <select name="practicante_id" class="form-select border-info" required>
                                            <option value="" disabled selected>Seleccione quién atiende...</option>
                                            @foreach($practicantes as $practicante)
                                                <option value="{{ $practicante->id }}">
                                                    {{ $practicante->nombre }} {{ $practicante->apellido }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-text text-muted small">
                                        Responsable de entregar el equipo.
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Fecha y Hora</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-clock"></i></span>
                                        <input type="text" class="form-control bg-light"
                                            value="{{ now()->format('d/m/Y h:i A') }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Fecha Esperada de Devolución <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="date" name="fecha_devolucion_esperada" id="fecha_devolucion_esperada"
                                            class="form-control" min="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="form-text text-muted small">
                                        Fecha límite para la devolución del equipo.
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Estado de entrega / Observaciones</label>
                                    <textarea name="observaciones" rows="2" class="form-control"
                                        placeholder="Ej: Se entrega con cargador original, sin mouse..."></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('prestamos.index') }}" class="btn btn-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Confirmar Préstamo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Estilos para el autocompletado */
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0.375rem;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .search-results.show {
            display: block;
        }

        .search-result-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }

        .search-result-item:hover {
            background-color: #f8f9fa;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item strong {
            display: block;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .search-result-item small {
            color: #6c757d;
        }

        .no-results {
            padding: 15px;
            text-align: center;
            color: #6c757d;
        }

        .loading-results {
            padding: 15px;
            text-align: center;
            color: #0d6efd;
        }
    </style>

    <script>
        let timeoutEstudiante;
        let timeoutEquipo;

        // ============================================
        // BÚSQUEDA DE ESTUDIANTE
        // ============================================
        document.getElementById('buscarEstudiante').addEventListener('input', function (e) {
            const query = e.target.value.trim();
            const resultadosDiv = document.getElementById('resultadosEstudiante');

            clearTimeout(timeoutEstudiante);

            if (query.length < 2) {
                resultadosDiv.classList.remove('show');
                return;
            }

            resultadosDiv.innerHTML = '<div class="loading-results"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';
            resultadosDiv.classList.add('show');

            timeoutEstudiante = setTimeout(() => {
                fetch(`{{ route('estudiantes.buscar') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            resultadosDiv.innerHTML = '<div class="no-results"><i class="fas fa-search"></i> No se encontraron estudiantes</div>';
                            return;
                        }

                        let html = '';
                        data.forEach(estudiante => {
                            html += `
                                                                <div class="search-result-item" onclick="seleccionarEstudiante(${estudiante.id}, '${estudiante.nombre}', '${estudiante.apellido}', '${estudiante.cedula}', '${estudiante.carrera}')">
                                                                    <strong>${estudiante.nombre} ${estudiante.apellido}</strong>
                                                                    <small>Cédula: ${estudiante.cedula} | ${estudiante.carrera}</small>
                                                                </div>
                                                            `;
                        });
                        resultadosDiv.innerHTML = html;
                    })
                    .catch(error => {
                        resultadosDiv.innerHTML = '<div class="no-results text-danger"><i class="fas fa-exclamation-triangle"></i> Error en la búsqueda</div>';
                    });
            }, 300);
        });

        function seleccionarEstudiante(id, nombre, apellido, cedula, carrera) {
            document.getElementById('estudiante_id').value = id;
            document.getElementById('buscarEstudiante').value = '';
            document.getElementById('resultadosEstudiante').classList.remove('show');

            document.getElementById('estudianteNombre').textContent = `${nombre} ${apellido}`;
            document.getElementById('estudianteDetalle').textContent = `Cédula: ${cedula} | ${carrera}`;
            document.getElementById('estudianteSeleccionado').style.display = 'block';
            document.getElementById('buscarEstudiante').style.display = 'none';
        }

        function limpiarEstudiante() {
            document.getElementById('estudiante_id').value = '';
            document.getElementById('buscarEstudiante').value = '';
            document.getElementById('estudianteSeleccionado').style.display = 'none';
            document.getElementById('buscarEstudiante').style.display = 'block';
            document.getElementById('buscarEstudiante').focus();
        }

        // ============================================
        // BÚSQUEDA DE EQUIPO
        // ============================================
        document.getElementById('buscarEquipo').addEventListener('input', function (e) {
            const query = e.target.value.trim();
            const resultadosDiv = document.getElementById('resultadosEquipo');

            clearTimeout(timeoutEquipo);

            if (query.length < 2) {
                resultadosDiv.classList.remove('show');
                return;
            }

            resultadosDiv.innerHTML = '<div class="loading-results"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';
            resultadosDiv.classList.add('show');

            timeoutEquipo = setTimeout(() => {
                fetch(`{{ route('equipos.buscar') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            resultadosDiv.innerHTML = '<div class="no-results"><i class="fas fa-search"></i> No hay equipos disponibles</div>';
                            return;
                        }

                        let html = '';
                        data.forEach(equipo => {
                            html += `
                                                                <div class="search-result-item" onclick="seleccionarEquipo(${equipo.id}, '${equipo.tipo}', '${equipo.marca}', '${equipo.modelo || ''}', '${equipo.nombre_equipo}')">
                                                                    <strong>Nombre: ${equipo.nombre_equipo}</strong>
                                                                    <small>${equipo.tipo} ${equipo.marca} - Modelo ${equipo.modelo || 'N/A'}</small>
                                                                </div>
                                                            `;
                        });
                        resultadosDiv.innerHTML = html;
                    })
                    .catch(error => {
                        resultadosDiv.innerHTML = '<div class="no-results text-danger"><i class="fas fa-exclamation-triangle"></i> Error en la búsqueda</div>';
                    });
            }, 300);
        });

        function seleccionarEquipo(id, tipo, marca, modelo, codigo, serie) {
            document.getElementById('equipo_id').value = id;
            document.getElementById('buscarEquipo').value = '';
            document.getElementById('resultadosEquipo').classList.remove('show');

            document.getElementById('equipoNombre').textContent = `Nombre: ${codigo}`;
            document.getElementById('equipoDetalle').textContent = `${tipo} ${marca} - Modelo ${modelo || 'N/A'}`;
            document.getElementById('equipoSeleccionado').style.display = 'block';
            document.getElementById('buscarEquipo').style.display = 'none';
        }

        function limpiarEquipo() {
            document.getElementById('equipo_id').value = '';
            document.getElementById('buscarEquipo').value = '';
            document.getElementById('equipoSeleccionado').style.display = 'none';
            document.getElementById('buscarEquipo').style.display = 'block';
            document.getElementById('buscarEquipo').focus();
        }

        // ============================================
        // CERRAR RESULTADOS AL HACER CLIC FUERA
        // ============================================
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#buscarEstudiante') && !e.target.closest('#resultadosEstudiante')) {
                document.getElementById('resultadosEstudiante').classList.remove('show');
            }
            if (!e.target.closest('#buscarEquipo') && !e.target.closest('#resultadosEquipo')) {
                document.getElementById('resultadosEquipo').classList.remove('show');
            }
        });

        // ============================================
        // VALIDACIÓN DE FORMULARIO
        // ============================================
        document.getElementById('formPrestamo').addEventListener('submit', function (e) {
            const estudianteId = document.getElementById('estudiante_id').value;
            const equipoId = document.getElementById('equipo_id').value;

            if (!estudianteId) {
                e.preventDefault();
                alert('Por favor, selecciona un estudiante de los resultados de búsqueda');
                document.getElementById('buscarEstudiante').focus();
                return false;
            }

            if (!equipoId) {
                e.preventDefault();
                alert('Por favor, selecciona un equipo de los resultados de búsqueda');
                document.getElementById('buscarEquipo').focus();
                return false;
            }
        });

        // ============================================
        // ESTABLECER FECHA DE DEVOLUCIÓN AUTOMÁTICA
        // ============================================
        document.addEventListener('DOMContentLoaded', function () {
            const fechaDevolucion = document.getElementById('fecha_devolucion_esperada');
            if (fechaDevolucion && !fechaDevolucion.value) {
                // Establecer la fecha de hoy en formato YYYY-MM-DD
                const hoy = new Date();
                const year = hoy.getFullYear();
                const month = String(hoy.getMonth() + 1).padStart(2, '0');
                const day = String(hoy.getDate()).padStart(2, '0');
                fechaDevolucion.value = `${year}-${month}-${day}`;
            }
        });

        // ============================================
        // POLLING INVISIBLE - ACTUALIZACIÓN DE ESTADOS
        // ============================================
        let intervaloPolling;

        function actualizarEstadosEquipos() {
            fetch('/api/equipos/estados')
                .then(response => response.json())
                .then(equipos => {
                    equipos.forEach(equipo => {
                        const elementoEquipo = document.querySelector(`[data-equipo-id="${equipo.id}"]`);

                        if (elementoEquipo) {
                            const estadoActual = elementoEquipo.dataset.estado;

                            if (estadoActual !== equipo.estado) {
                                elementoEquipo.dataset.estado = equipo.estado;

                                const badge = elementoEquipo.querySelector('.badge');
                                if (badge) {
                                    if (equipo.estado === 'disponible') {
                                        badge.className = 'badge bg-success';
                                        badge.textContent = 'Disponible';
                                    } else if (equipo.estado === 'prestado') {
                                        badge.className = 'badge bg-danger';
                                        badge.textContent = 'Prestado';
                                    } else if (equipo.estado === 'mantenimiento') {
                                        badge.className = 'badge bg-warning text-dark';
                                        badge.textContent = 'Mantenimiento';
                                    } else if (equipo.estado === 'baja') {
                                        badge.className = 'badge bg-secondary';
                                        badge.textContent = 'Baja';
                                    }
                                }

                                const botonSeleccionar = elementoEquipo.querySelector('.btn-outline-success');
                                if (botonSeleccionar) {
                                    if (equipo.estado !== 'disponible') {
                                        botonSeleccionar.disabled = true;
                                        botonSeleccionar.classList.add('disabled');
                                    } else {
                                        botonSeleccionar.disabled = false;
                                        botonSeleccionar.classList.remove('disabled');
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error actualizando estados:', error));
        }

        function iniciarPolling() {
            actualizarEstadosEquipos();
            intervaloPolling = setInterval(actualizarEstadosEquipos, 10000);
        }

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                if (intervaloPolling) {
                    clearInterval(intervaloPolling);
                }
            } else {
                iniciarPolling();
            }
        });

        iniciarPolling();
    </script>
@endsection