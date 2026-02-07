@extends('layouts.panel')

@section('titulo', 'Nuevo Préstamo')

@section('contenido')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-secondary">
                                <i class="fas fa-hand-holding me-2"></i>Registrar Salida de Equipo
                            </h5>
                            <a href="{{ route('estudiantes.create') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-user-plus me-1"></i> Registrar estudiante
                            </a>
                        </div>
                    </div>

                    {{-- Mostrar errores --}}
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
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="card-body p-4">
                        <form action="{{ route('prestamos.store') }}" method="POST" id="formPrestamo">
                            @csrf

                            <div class="row g-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-0">Datos del Préstamo</h6>
                                </div>

                                <!-- ESTUDIANTE SOLICITANTE -->
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between mb-1">
                                        <label class="form-label fw-bold">Estudiante Solicitante <span
                                                class="text-danger">*</span></label>
                                    </div>

                                    <div class="position-relative">
                                        <div class="input-group" id="inputGroupEstudiante">
                                            <span class="input-group-text bg-light">
                                                <i class="fas fa-user-graduate"></i>
                                            </span>
                                            <input type="text" id="buscarEstudiante" class="form-control"
                                                placeholder="Buscar por cédula o nombre..." autocomplete="off">
                                        </div>

                                        <input type="hidden" name="estudiante_id" id="estudiante_id" required>
                                        <div id="resultadosEstudiante" class="search-results"></div>

                                        <div id="estudianteSeleccionado" class="mt-3" style="display: none;">
                                            <div class="card border-success border-2">
                                                <div class="card-body p-3 bg-success bg-opacity-10">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-success bg-opacity-25 p-2 rounded me-3">
                                                                <i class="fas fa-user-graduate fa-lg text-success"></i>
                                                            </div>
                                                            <div>
                                                                <strong class="d-block" id="estudianteNombre"></strong>
                                                                <small class="text-muted" id="estudianteDetalle"></small>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-danger btn-sm rounded-circle"
                                                            onclick="limpiarEstudiante()" title="Cambiar estudiante"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- EQUIPOS A ENTREGAR -->
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between mb-1">
                                        <label class="form-label fw-bold mb-0">Equipos a Entregar <span
                                                class="text-danger">*</span></label>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="agregarFilaEquipo()">
                                            <i class="fas fa-plus me-1"></i> Añadir equipo/accesorio
                                        </button>
                                    </div>

                                    <div id="contenedorEquipos"></div>

                                    <div class="form-text text-success mt-2">
                                        <i class="fas fa-info-circle me-1"></i>Solo se muestran equipos disponibles.
                                    </div>
                                </div>

                                <!-- DETALLES Y RESPONSABLES -->
                                <div class="col-12 mt-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-0">Detalles y Responsables</h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Atendido por (Practicante) <span
                                            class="text-danger">*</span></label>
                                    <select name="practicante_id" class="form-select" required>
                                        <option value="" disabled selected>Seleccione quién atiende...</option>
                                        @foreach($practicantes as $practicante)
                                            <option value="{{ $practicante->id }}">
                                                {{ $practicante->nombre }} {{ $practicante->apellido }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted small">Responsable de entregar el equipo.</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Fecha Esperada de Devolución <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="fecha_devolucion_esperada" id="fecha_devolucion_esperada"
                                        class="form-control" required min="{{ date('Y-m-d') }}">
                                    <div class="form-text text-muted small">Fecha límite para la devolución del equipo.
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Estado de entrega / Observaciones</label>
                                    <textarea name="observaciones" rows="1" class="form-control"
                                        placeholder="Ej: Se entrega con cargador original..."></textarea>
                                </div>
                            </div>

                            <!-- BOTONES DE ACCIÓN -->
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary px-4">
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
            z-index: 1050;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
            margin-top: 4px;
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

        .btn-danger.rounded-circle {
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
            transition: all 0.2s;
        }

        .btn-danger.rounded-circle:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.5);
        }
    </style>

    <script>
        let timeoutEstudiante;
        let timeoutsEquipos = {};
        let equipoCounter = 0;

        // ===== BÚSQUEDA DE ESTUDIANTE =====
        const inputEstudiante = document.getElementById('buscarEstudiante');
        const resultadosEstudiante = document.getElementById('resultadosEstudiante');
        const estudianteIdInput = document.getElementById('estudiante_id');

        inputEstudiante.addEventListener('input', function (e) {
            const query = e.target.value.trim();
            if (timeoutEstudiante) clearTimeout(timeoutEstudiante);

            if (query.length < 2) {
                resultadosEstudiante.classList.remove('show');
                return;
            }

            resultadosEstudiante.innerHTML = '<div class="p-3 text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';
            resultadosEstudiante.classList.add('show');

            timeoutEstudiante = setTimeout(() => {
                fetch(`{{ route('estudiantes.buscar') }}?q=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.length === 0) {
                            resultadosEstudiante.innerHTML = '<div class="p-3 text-center text-muted">No se encontraron estudiantes</div>';
                            return;
                        }
                        let html = '';
                        data.forEach(est => {
                            html += `<div class="search-result-item" onclick='seleccionarEstudiante(${JSON.stringify(est)})'>
                                        <strong>${est.nombre} ${est.apellido}</strong><br>
                                        <small>Cédula: ${est.cedula || 'N/A'} | ${est.carrera || 'Sin carrera'}</small>
                                    </div>`;
                        });
                        resultadosEstudiante.innerHTML = html;
                    });
            }, 300);
        });

        function seleccionarEstudiante(est) {
            estudianteIdInput.value = est.id;
            document.getElementById('estudianteNombre').textContent = `${est.nombre} ${est.apellido}`;
            document.getElementById('estudianteDetalle').textContent = `Cédula: ${est.cedula || 'N/A'} | ${est.carrera || 'Sin carrera'}`;
            document.getElementById('inputGroupEstudiante').style.display = 'none';
            document.getElementById('estudianteSeleccionado').style.display = 'block';
            resultadosEstudiante.classList.remove('show');
        }

        function limpiarEstudiante() {
            estudianteIdInput.value = '';
            document.getElementById('inputGroupEstudiante').style.display = 'flex';
            document.getElementById('estudianteSeleccionado').style.display = 'none';
            inputEstudiante.focus();
        }

        // ===== GESTIÓN DE EQUIPOS =====
        function agregarFilaEquipo() {
            equipoCounter++;
            const html = `
                        <div class="equipo-row mb-3" id="equipo-row-${equipoCounter}">
                            <div class="position-relative">
                                <div class="input-group" id="inputGroupEquipo_${equipoCounter}">
                                    <span class="input-group-text bg-light"><i class="fas fa-laptop"></i></span>
                                    <input type="text" id="buscarEquipo_${equipoCounter}" class="form-control" 
                                        placeholder="Buscar equipo ${equipoCounter}..." autocomplete="off">
                                </div>
                                <input type="hidden" name="equipo_id[]" id="equipo_id_${equipoCounter}" required>
                                <div id="resultadosEquipo_${equipoCounter}" class="search-results"></div>

                                <div id="equipoSeleccionado_${equipoCounter}" class="mt-2" style="display:none;">
                                    <div class="card border-info border-2">
                                        <div class="card-body p-3 bg-info bg-opacity-10">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div id="equipoNombre_${equipoCounter}"></div>
                                                <button type="button" class="btn btn-danger btn-sm rounded-circle" 
                                                    onclick="limpiarEquipo(${equipoCounter})" style="width:32px;height:32px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
            document.getElementById('contenedorEquipos').insertAdjacentHTML('beforeend', html);
            inicializarBusquedaEquipo(equipoCounter);
        }

        function inicializarBusquedaEquipo(id) {
            const input = document.getElementById(`buscarEquipo_${id}`);
            const resultados = document.getElementById(`resultadosEquipo_${id}`);

            input.addEventListener('input', function (e) {
                const query = e.target.value.trim();
                if (timeoutsEquipos[id]) clearTimeout(timeoutsEquipos[id]);

                if (query.length < 2) {
                    resultados.classList.remove('show');
                    return;
                }

                resultados.innerHTML = '<div class="p-3 text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';
                resultados.classList.add('show');

                timeoutsEquipos[id] = setTimeout(() => {
                    fetch(`{{ route('equipos.buscar') }}?q=${encodeURIComponent(query)}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.length === 0) {
                                resultados.innerHTML = '<div class="p-3 text-center text-muted">No hay equipos disponibles</div>';
                                return;
                            }

                            // Filtrar equipos ya seleccionados
                            const seleccionados = Array.from(document.querySelectorAll('input[name="equipo_id[]"]'))
                                .map(i => i.value).filter(v => v);
                            const disponibles = data.filter(eq => !seleccionados.includes(eq.id.toString()));

                            if (disponibles.length === 0) {
                                resultados.innerHTML = '<div class="p-3 text-center text-muted">Todos ya seleccionados</div>';
                                return;
                            }

                            let html = '';
                            disponibles.forEach(eq => {
                                const equipoJSON = JSON.stringify(eq).replace(/'/g, '&#39;');
                                html += `<div class="search-result-item" onclick='seleccionarEquipo(${equipoJSON}, ${id})'>
                                            ${eq.display || eq.nombre_equipo}
                                        </div>`;
                            });
                            resultados.innerHTML = html;
                        });
                }, 300);
            });
        }

        function seleccionarEquipo(equipo, rowIndex) {
            document.getElementById(`equipo_id_${rowIndex}`).value = equipo.id;
            document.getElementById(`inputGroupEquipo_${rowIndex}`).style.display = 'none';
            document.getElementById(`resultadosEquipo_${rowIndex}`).classList.remove('show');

            let displayText = '';
            let cantidadHTML = '';

            if (equipo.es_individual) {
                // ===================================================================
                // LAPTOP (EQUIPO INDIVIDUAL): Sin selector de cantidad
                // ===================================================================
                displayText = `<strong>${equipo.nombre_equipo}</strong><br>
                            <small class="text-muted">${equipo.marca} - Modelo ${equipo.modelo}</small>`;

                // Campo oculto con cantidad=1 para laptops
                cantidadHTML = `<input type="hidden" name="equipo_cantidad[]" value="1">`;
            } else {
                // ===================================================================
                // EQUIPO POR CANTIDAD: Mostrar selector de cantidad
                // ===================================================================
                displayText = `<strong>${equipo.nombre_equipo}</strong><br>
                            <small class="text-muted">${equipo.marca} - Modelo ${equipo.modelo}</small><br>
                            <span class="badge bg-info mt-1">Disponibles: ${equipo.cantidad_disponible}/${equipo.cantidad_total}</span>`;

                // Campo numérico para seleccionar cantidad
                cantidadHTML = `
                        <div class="mt-2">
                            <label class="form-label fw-bold small mb-1">
                                <i class="fas fa-hashtag me-1"></i>Cantidad a prestar
                            </label>
                            <input type="number" 
                                name="equipo_cantidad[]" 
                                id="cantidad_${rowIndex}"
                                class="form-control form-control-sm" 
                                min="1" 
                                max="${equipo.cantidad_disponible}" 
                                value="1" 
                                required>
                            <small class="text-muted">Máximo: ${equipo.cantidad_disponible} unidad(es) disponibles</small>
                        </div>`;
            }

            document.getElementById(`equipoNombre_${rowIndex}`).innerHTML = displayText + cantidadHTML;
            document.getElementById(`equipoSeleccionado_${rowIndex}`).style.display = 'block';
        }

        function limpiarEquipo(id) {
            document.getElementById(`equipo_id_${id}`).value = '';
            document.getElementById(`buscarEquipo_${id}`).value = '';
            document.getElementById(`inputGroupEquipo_${id}`).style.display = 'flex';
            document.getElementById(`equipoSeleccionado_${id}`).style.display = 'none';
        }

        // ===== INICIALIZACIÓN =====
        document.addEventListener('DOMContentLoaded', () => {
            // Agregar primera fila de equipo
            agregarFilaEquipo();

            // Establecer fecha de hoy automáticamente
            const fechaDevolucion = document.getElementById('fecha_devolucion_esperada');
            if (fechaDevolucion && !fechaDevolucion.value) {
                const hoy = new Date();
                const year = hoy.getFullYear();
                const month = String(hoy.getMonth() + 1).padStart(2, '0');
                const day = String(hoy.getDate()).padStart(2, '0');
                fechaDevolucion.value = `${year}-${month}-${day}`;
            }
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-results') && !e.target.closest('input')) {
                document.querySelectorAll('.search-results').forEach(el => el.classList.remove('show'));
            }
        });
    </script>
@endsection