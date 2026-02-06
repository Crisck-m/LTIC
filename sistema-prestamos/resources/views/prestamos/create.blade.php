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
                                            <span class="input-group-text bg-light" id="iconoEstudiante">
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

        .equipo-row {
            position: relative;
            transition: all 0.3s ease;
        }

        /* Botones X más notorios */
        .btn-danger.rounded-circle {
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
            transition: all 0.2s;
        }

        .btn-danger.rounded-circle:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.5);
        }

        /* Cards de selección */
        .card.border-success,
        .card.border-info {
            transition: all 0.3s ease;
        }

        .card.border-success:hover,
        .card.border-info:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>

    <script>
        let timeoutEstudiante;
        let timeoutsEquipos = {};
        let equipoCounter = 0;

        // ============================================
        // BÚSQUEDA DE ESTUDIANTE
        // ============================================
        const inputEstudiante = document.getElementById('buscarEstudiante');
        const resultadosEstudiante = document.getElementById('resultadosEstudiante');
        const estudianteIdInput = document.getElementById('estudiante_id');
        const estudianteSeleccionadoDiv = document.getElementById('estudianteSeleccionado');
        const estudianteNombreSpan = document.getElementById('estudianteNombre');
        const estudianteDetalleSpan = document.getElementById('estudianteDetalle');
        const inputGroupEstudiante = document.getElementById('inputGroupEstudiante');

        inputEstudiante.addEventListener('input', function (e) {
            const query = e.target.value.trim();

            if (timeoutEstudiante) clearTimeout(timeoutEstudiante);

            if (query.length < 2) {
                resultadosEstudiante.classList.remove('show');
                return;
            }

            resultadosEstudiante.innerHTML = '<div class="p-3 text-center text-primary"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';
            resultadosEstudiante.classList.add('show');

            timeoutEstudiante = setTimeout(() => {
                fetch(`{{ route('estudiantes.buscar') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            resultadosEstudiante.innerHTML = '<div class="p-3 text-center text-muted">No se encontraron estudiantes</div>';
                            return;
                        }

                        let html = '';
                        data.forEach(estudiante => {
                            const cedula = estudiante.cedula || 'Sin cédula';
                            const carrera = estudiante.carrera || 'Sin carrera';
                            const nombre = (estudiante.nombre || '').replace(/'/g, '&#39;');
                            const apellido = (estudiante.apellido || '').replace(/'/g, '&#39;');
                            const cedulaEscaped = cedula.replace(/'/g, '&#39;');
                            const carreraEscaped = carrera.replace(/'/g, '&#39;');

                            html += `
                                                                        <div class="search-result-item" onclick='seleccionarEstudiante(${estudiante.id}, "${nombre}", "${apellido}", "${cedulaEscaped}", "${carreraEscaped}")'>
                                                                            <strong>${estudiante.nombre} ${estudiante.apellido}</strong>
                                                                            <small>Cédula: ${cedula} | ${carrera}</small>
                                                                        </div>
                                                                    `;
                        });
                        resultadosEstudiante.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultadosEstudiante.innerHTML = '<div class="p-3 text-center text-danger">Error en la búsqueda</div>';
                    });
            }, 300);
        });

        function seleccionarEstudiante(id, nombre, apellido, cedula, carrera) {
            estudianteIdInput.value = id;
            inputEstudiante.value = '';
            resultadosEstudiante.classList.remove('show');

            estudianteNombreSpan.textContent = `${nombre} ${apellido}`;
            estudianteDetalleSpan.textContent = `Cédula: ${cedula} | ${carrera}`;

            // Ocultar input y mostrar card
            inputGroupEstudiante.style.display = 'none';
            estudianteSeleccionadoDiv.style.display = 'block';
        }

        function limpiarEstudiante() {
            estudianteIdInput.value = '';
            inputEstudiante.value = '';
            estudianteSeleccionadoDiv.style.display = 'none';
            inputGroupEstudiante.style.display = 'flex';
            inputEstudiante.focus();
        }

        // ============================================
        // GESTIÓN DE EQUIPOS MÚLTIPLES
        // ============================================
        function agregarFilaEquipo() {
            equipoCounter++;
            const container = document.getElementById('contenedorEquipos');
            const rowId = `equipo-row-${equipoCounter}`;

            const html = `
                                                        <div class="equipo-row mb-3" id="${rowId}">
                                                            <div class="position-relative">
                                                                <div class="input-group" id="inputGroupEquipo_${equipoCounter}">
                                                                    <span class="input-group-text bg-light">
                                                                        <i class="fas fa-laptop"></i>
                                                                    </span>
                                                                    <input type="text" id="buscarEquipo_${equipoCounter}" class="form-control"
                                                                        placeholder="Buscar equipo ${equipoCounter}..." autocomplete="off">
                                                                </div>

                                                                <input type="hidden" name="equipo_id[]" id="equipo_id_${equipoCounter}" required>
                                                                <div id="resultadosEquipo_${equipoCounter}" class="search-results"></div>

                                                                <div id="equipoSeleccionado_${equipoCounter}" class="mt-2" style="display: none;">
                                                                    <div class="card border-info border-2">
                                                                        <div class="card-body p-3 bg-info bg-opacity-10">
                                                                            <div class="d-flex align-items-center justify-content-between">
                                                                                <div class="d-flex align-items-center">
                                                                                    <div class="bg-info bg-opacity-25 p-2 rounded me-3">
                                                                                        <i class="fas fa-laptop fa-lg text-info"></i>
                                                                                    </div>
                                                                                    <div>
                                                                                        <strong class="d-block" id="equipoNombre_${equipoCounter}"></strong>
                                                                                        <small class="text-muted" id="equipoDetalle_${equipoCounter}"></small>
                                                                                    </div>
                                                                                </div>
                                                                                <button type="button" class="btn btn-danger btn-sm rounded-circle" 
                                                                                    onclick="limpiarEquipo(${equipoCounter})" title="Cambiar equipo"
                                                                                    style="width: 32px; height: 32px;">
                                                                                    <i class="fas fa-times"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    `;

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            container.appendChild(tempDiv.firstElementChild);

            inicializarBusquedaEquipo(equipoCounter);
            actualizarNumeracionEquipos();
        }

        function actualizarNumeracionEquipos() {
            const rows = document.querySelectorAll('.equipo-row');
            rows.forEach((row, index) => {
                const numero = index + 1;
                const input = row.querySelector('input[id^="buscarEquipo_"]');
                if (input) {
                    input.placeholder = `Buscar equipo ${numero}...`;
                }
            });
        }

        function inicializarBusquedaEquipo(id) {
            const input = document.getElementById(`buscarEquipo_${id}`);
            if (!input) return;

            const resultadosDiv = document.getElementById(`resultadosEquipo_${id}`);

            input.addEventListener('input', function (e) {
                const query = e.target.value.trim();

                if (timeoutsEquipos[id]) clearTimeout(timeoutsEquipos[id]);

                if (query.length < 2) {
                    resultadosDiv.classList.remove('show');
                    return;
                }

                resultadosDiv.innerHTML = '<div class="p-3 text-center text-primary"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';
                resultadosDiv.classList.add('show');

                timeoutsEquipos[id] = setTimeout(() => {
                    fetch(`{{ route('equipos.buscar') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length === 0) {
                                resultadosDiv.innerHTML = '<div class="p-3 text-center text-muted">No hay equipos disponibles</div>';
                                return;
                            }

                            const inputsIds = document.querySelectorAll('input[name="equipo_id[]"]');
                            const idsSeleccionados = Array.from(inputsIds).map(input => input.value).filter(val => val);

                            const filteredData = data.filter(eq => !idsSeleccionados.includes(eq.id.toString()));

                            if (filteredData.length === 0) {
                                resultadosDiv.innerHTML = '<div class="p-3 text-center text-muted">Equipos ya seleccionados</div>';
                                return;
                            }

                            let html = '';
                            filteredData.forEach(equipo => {
                                const modelo = (equipo.modelo || '').replace(/'/g, '&#39;');
                                const nombre = (equipo.nombre_equipo || '').replace(/'/g, '&#39;');
                                const marca = (equipo.marca || '').replace(/'/g, '&#39;');
                                const tipo = (equipo.tipo || '').replace(/'/g, '&#39;');

                                html += `
                                                                            <div class="search-result-item" onclick='seleccionarEquipo(${id}, ${equipo.id}, "${tipo}", "${marca}", "${modelo}", "${nombre}")'>
                                                                                <strong>${equipo.nombre_equipo}</strong>
                                                                                <small>${equipo.tipo} ${equipo.marca} - Modelo ${equipo.modelo || 'N/A'}</small>
                                                                            </div>
                                                                        `;
                            });
                            resultadosDiv.innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            resultadosDiv.innerHTML = '<div class="p-3 text-center text-danger">Error en la búsqueda</div>';
                        });
                }, 300);
            });
        }

        function seleccionarEquipo(rowId, equipoId, tipo, marca, modelo, codigo) {
            document.getElementById(`equipo_id_${rowId}`).value = equipoId;
            document.getElementById(`buscarEquipo_${rowId}`).value = '';
            document.getElementById(`resultadosEquipo_${rowId}`).classList.remove('show');

            document.getElementById(`equipoNombre_${rowId}`).textContent = `Nombre: ${codigo}`;
            document.getElementById(`equipoDetalle_${rowId}`).textContent = `${tipo} ${marca} - Modelo ${modelo || 'N/A'}`;

            // Ocultar input y mostrar card
            document.getElementById(`inputGroupEquipo_${rowId}`).style.display = 'none';
            document.getElementById(`equipoSeleccionado_${rowId}`).style.display = 'block';
        }

        function limpiarEquipo(rowId) {
            document.getElementById(`equipo_id_${rowId}`).value = '';
            document.getElementById(`buscarEquipo_${rowId}`).value = '';
            document.getElementById(`equipoSeleccionado_${rowId}`).style.display = 'none';
            document.getElementById(`inputGroupEquipo_${rowId}`).style.display = 'flex';
            document.getElementById(`buscarEquipo_${rowId}`).focus();
        }

        // ============================================
        // CERRAR RESULTADOS AL HACER CLIC FUERA
        // ============================================
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#buscarEstudiante') && !e.target.closest('#resultadosEstudiante')) {
                resultadosEstudiante.classList.remove('show');
            }
            if (!e.target.closest('.input-group') && !e.target.closest('.search-results')) {
                document.querySelectorAll('.search-results').forEach(el => el.classList.remove('show'));
            }
        });

        // ============================================
        // VALIDACIÓN DE FORMULARIO
        // ============================================
        document.getElementById('formPrestamo').addEventListener('submit', function (e) {
            const estudianteId = estudianteIdInput.value;
            const equipoInputs = document.querySelectorAll('input[name="equipo_id[]"]');
            let algunEquipoSeleccionado = false;

            equipoInputs.forEach(input => {
                if (input.value) algunEquipoSeleccionado = true;
            });

            if (!estudianteId) {
                e.preventDefault();
                alert('Por favor, selecciona un estudiante de los resultados de búsqueda');
                inputEstudiante.focus();
                return false;
            }

            if (!algunEquipoSeleccionado) {
                e.preventDefault();
                alert('Por favor, selecciona al menos un equipo.');
                return false;
            }
        });

        // ============================================
        // INICIALIZACIÓN
        // ============================================
        document.addEventListener('DOMContentLoaded', function () {
            const fechaDevolucion = document.getElementById('fecha_devolucion_esperada');
            if (fechaDevolucion && !fechaDevolucion.value) {
                const hoy = new Date();
                const year = hoy.getFullYear();
                const month = String(hoy.getMonth() + 1).padStart(2, '0');
                const day = String(hoy.getDate()).padStart(2, '0');
                fechaDevolucion.value = `${year}-${month}-${day}`;
            }

            agregarFilaEquipo();
        });
    </script>
@endsection