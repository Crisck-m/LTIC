@extends('layouts.panel')

@section('titulo', 'Editar Préstamo')

@section('contenido')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card shadow-sm border-primary">
                    <div class="card-header bg-primary bg-opacity-10 py-3">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-edit me-2"></i>Editar Préstamo Activo
                        </h5>
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
                        <form action="{{ route('prestamos.update', $prestamo->id) }}" method="POST" id="formPrestamo">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <!-- DATOS DEL PRÉSTAMO (SOLO LECTURA) -->
                                <div class="col-12">
                                    <h6 class="text-secondary border-bottom pb-2 mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Datos del Préstamo (Solo Lectura)
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Estudiante Solicitante</label>
                                    <div class="card border-2 border-secondary">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary bg-opacity-10 p-3 rounded me-3">
                                                    <i class="fas fa-user-graduate fa-2x text-secondary"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold fs-6">
                                                        {{ $prestamo->estudiante->nombre }}
                                                        {{ $prestamo->estudiante->apellido }}
                                                    </div>
                                                    <div class="text-muted small">
                                                        <i class="fas fa-graduation-cap me-1"></i>
                                                        {{ $prestamo->estudiante->carrera }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="estudiante_id" value="{{ $prestamo->estudiante_id }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-info">Atendido por (Practicante)</label>
                                    <div class="card border-2 border-info">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-info bg-opacity-10 p-3 rounded me-3">
                                                    <i class="fas fa-user-tie fa-2x text-info"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold fs-6">
                                                        {{ $prestamo->practicante->nombre }}
                                                        {{ $prestamo->practicante->apellido }}
                                                    </div>
                                                    <div class="text-muted small">
                                                        <i class="fas fa-hand-holding me-1"></i>
                                                        Responsable del registro original
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="practicante_id" value="{{ $prestamo->practicante_id }}">
                                </div>

                                <!-- EQUIPOS EN EL PRÉSTAMO -->
                                <div class="col-12 mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-primary border-bottom pb-2 mb-0 flex-grow-1">
                                            <i class="fas fa-laptop me-2"></i>Equipos en este Préstamo
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-primary"
                                            onclick="mostrarAgregarEquipo()">
                                            <i class="fas fa-plus me-1"></i> Agregar Equipo
                                        </button>
                                    </div>

                                    <div class="alert alert-info d-flex align-items-start">
                                        <i class="fas fa-lightbulb me-2 mt-1"></i>
                                        <div>
                                            <strong>¿Cómo editar?</strong>
                                            <ul class="mb-0 mt-1 small">
                                                <li>Desmarca un equipo para <strong>quitarlo</strong> del préstamo</li>
                                                <li>Haz clic en <strong>"Agregar Equipo"</strong> para añadir más</li>
                                                <li>Los cambios se aplicarán al guardar</li>
                                            </ul>
                                        </div>
                                    </div>

                                    @php
                                        $equiposActivos = $prestamo->prestamoEquipos()->where('estado', 'activo')->with('equipo')->get();
                                    @endphp

                                    <!-- EQUIPOS ACTUALES -->
                                    <div id="equiposActualesContainer">
                                        @if($equiposActivos->count() > 0)
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="mantenerTodos" checked>
                                                    <label class="form-check-label fw-bold" for="mantenerTodos">
                                                        Mantener todos los equipos ({{ $equiposActivos->count() }})
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                @foreach($equiposActivos as $index => $pe)
                                                    <div class="col-md-6">
                                                        <div class="card border-2 equipo-card-edit"
                                                            data-equipo-id="{{ $pe->equipo_id }}">
                                                            <div class="card-body">
                                                                <div class="form-check">
                                                                    <input class="form-check-input equipo-checkbox-edit"
                                                                        type="checkbox" name="equipo_id[]"
                                                                        value="{{ $pe->equipo_id }}"
                                                                        id="equipo_actual_{{ $pe->equipo_id }}" checked>
                                                                    <label class="form-check-label w-100"
                                                                        for="equipo_actual_{{ $pe->equipo_id }}">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                                                                <i class="fas fa-laptop fa-2x text-primary"></i>
                                                                            </div>
                                                                            <div class="flex-grow-1">
                                                                                <div class="fw-bold">{{ $pe->equipo->tipo }}</div>
                                                                                <div class="text-muted small">
                                                                                    {{ $pe->equipo->marca }} -
                                                                                    {{ $pe->equipo->modelo }}
                                                                                </div>
                                                                                <span
                                                                                    class="badge bg-secondary">{{ $pe->equipo->nombre_equipo }}</span>
                                                                                @if($loop->first)
                                                                                    <span class="badge bg-warning text-dark ms-1">
                                                                                        <i class="fas fa-star me-1"></i>Original
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                No hay equipos activos en este préstamo.
                                            </div>
                                        @endif
                                    </div>

                                    <!-- NUEVOS EQUIPOS A AGREGAR -->
                                    <div id="nuevosEquiposContainer" class="mt-4" style="display: none;">
                                        <h6 class="text-success mb-3">
                                            <i class="fas fa-plus-circle me-2"></i>Nuevos Equipos a Agregar
                                        </h6>
                                        <div id="contenedorNuevosEquipos"></div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="alert alert-warning d-none" id="alertaEquiposVacio">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Atención:</strong> Debes mantener al menos un equipo en el préstamo.
                                        </div>
                                    </div>
                                </div>

                                <!-- DETALLES ADICIONALES -->
                                <div class="col-12 mt-4">
                                    <h6 class="text-secondary border-bottom pb-2 mb-3">
                                        <i class="fas fa-calendar-alt me-2"></i>Detalles del Préstamo
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fecha Esperada de Devolución <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="date" name="fecha_devolucion_esperada" class="form-control"
                                            value="{{ \Carbon\Carbon::parse($prestamo->fecha_devolucion_esperada)->format('Y-m-d') }}"
                                            min="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="form-text text-muted small">Fecha límite para devolver los equipos</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Estado de entrega / Observaciones</label>
                                    <textarea name="observaciones" rows="3" class="form-control"
                                        placeholder="Ej: Se entrega con cargador, sin mouse...">{{ $prestamo->observaciones_prestamo }}</textarea>
                                    <div class="form-text text-muted small">Detalles sobre el estado de los equipos</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('prestamos.index') }}" class="btn btn-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary px-4" id="btnGuardar">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .equipo-card-edit {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .equipo-card-edit:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-color: #0d6efd !important;
        }

        .equipo-checkbox-edit:checked~label {
            font-weight: bold;
        }

        .equipo-card-edit:has(.equipo-checkbox-edit:checked) {
            background-color: #e7f3ff;
            border-color: #0d6efd !important;
        }

        .equipo-card-edit:has(.equipo-checkbox-edit:not(:checked)) {
            opacity: 0.6;
            background-color: #f8f9fa;
        }

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

        .nuevo-equipo-card {
            background-color: #e8f5e9;
            border-color: #4caf50 !important;
        }
    </style>

    <script>
        let equipoNuevoCounter = 0;
        let timeoutsEquipos = {};

        document.addEventListener('DOMContentLoaded', function () {
            const mantenerTodos = document.getElementById('mantenerTodos');
            const checkboxesEquipos = document.querySelectorAll('.equipo-checkbox-edit');
            const alertaVacio = document.getElementById('alertaEquiposVacio');
            const btnGuardar = document.getElementById('btnGuardar');
            const form = document.getElementById('formPrestamo');

            // Función principal de verificación
            function verificarYActualizarEstado() {
                const equiposActivos = Array.from(checkboxesEquipos).filter(cb => cb.checked).length;

                // Contar equipos nuevos que REALMENTE tienen valor
                const equiposNuevosInputs = document.querySelectorAll('input[name="equipos_nuevos[]"]');
                const equiposNuevosConValor = Array.from(equiposNuevosInputs).filter(input => input.value && input.value.trim() !== '').length;

                const totalEquipos = equiposActivos + equiposNuevosConValor;

                console.log('DEBUG - Equipos activos:', equiposActivos);
                console.log('DEBUG - Equipos nuevos con valor:', equiposNuevosConValor);
                console.log('DEBUG - Total equipos:', totalEquipos);

                // Verificar si se está reemplazando el equipo original
                const primerCheckbox = checkboxesEquipos[0];
                const equipoOriginalDesmarcado = primerCheckbox && !primerCheckbox.checked;

                if (equipoOriginalDesmarcado && equiposNuevosConValor > 0) {
                    // CASO: Reemplazo del equipo original
                    mostrarMensajeReemplazo();
                    alertaVacio.classList.add('d-none');
                    btnGuardar.disabled = false;
                } else if (totalEquipos === 0) {
                    // CASO: No hay equipos
                    ocultarMensajeReemplazo();
                    alertaVacio.classList.remove('d-none');
                    btnGuardar.disabled = true;
                } else {
                    // CASO: Todo normal
                    ocultarMensajeReemplazo();
                    alertaVacio.classList.add('d-none');
                    btnGuardar.disabled = false;
                }
            }

            function mostrarMensajeReemplazo() {
                let alertaReemplazo = document.getElementById('alertaReemplazo');

                if (!alertaReemplazo) {
                    alertaReemplazo = document.createElement('div');
                    alertaReemplazo.id = 'alertaReemplazo';
                    alertaReemplazo.className = 'alert alert-warning d-flex align-items-start mt-3';
                    alertaReemplazo.innerHTML = `
                                <i class="fas fa-exchange-alt me-2 mt-1 fa-lg"></i>
                                <div>
                                    <strong>🔄 Reemplazo de Equipo Original</strong>
                                    <p class="mb-0 mt-1">
                                        Estás <strong>reemplazando</strong> el equipo original por el nuevo equipo seleccionado.
                                        El equipo original se marcará como devuelto y el nuevo se asignará al préstamo.
                                    </p>
                                </div>
                            `;

                    const container = document.getElementById('equiposActualesContainer');
                    container.appendChild(alertaReemplazo);
                } else {
                    alertaReemplazo.classList.remove('d-none');
                }
            }

            function ocultarMensajeReemplazo() {
                const alertaReemplazo = document.getElementById('alertaReemplazo');
                if (alertaReemplazo) {
                    alertaReemplazo.classList.add('d-none');
                }
            }

            // Event listeners para checkboxes
            if (mantenerTodos) {
                mantenerTodos.addEventListener('change', function () {
                    checkboxesEquipos.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    verificarYActualizarEstado();
                });
            }

            checkboxesEquipos.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const todosSeleccionados = Array.from(checkboxesEquipos).every(cb => cb.checked);
                    const algunoSeleccionado = Array.from(checkboxesEquipos).some(cb => cb.checked);

                    if (mantenerTodos) {
                        mantenerTodos.checked = todosSeleccionados;
                        mantenerTodos.indeterminate = algunoSeleccionado && !todosSeleccionados;
                    }

                    verificarYActualizarEstado();
                });
            });

            // Click en card también marca checkbox
            document.querySelectorAll('.equipo-card-edit').forEach(card => {
                card.addEventListener('click', function (e) {
                    if (e.target.type !== 'checkbox' && !e.target.closest('label')) {
                        const checkbox = this.querySelector('.equipo-checkbox-edit');
                        if (checkbox) {
                            checkbox.checked = !checkbox.checked;
                            checkbox.dispatchEvent(new Event('change'));
                        }
                    }
                });
            });

            // Hacer función accesible globalmente
            window.verificarYActualizarEstado = verificarYActualizarEstado;

            // Validación al enviar
            form.addEventListener('submit', function (e) {
                const equiposActivos = Array.from(checkboxesEquipos).filter(cb => cb.checked).length;
                const equiposNuevosInputs = document.querySelectorAll('input[name="equipos_nuevos[]"]');
                const equiposNuevosConValor = Array.from(equiposNuevosInputs).filter(input => input.value && input.value.trim() !== '').length;

                console.log('SUBMIT - Equipos activos:', equiposActivos);
                console.log('SUBMIT - Equipos nuevos:', equiposNuevosConValor);

                if (equiposActivos + equiposNuevosConValor === 0) {
                    e.preventDefault();
                    alert('⚠️ El préstamo no puede quedar vacío.\n\nDebes:\n• Mantener al menos un equipo actual, O\n• Agregar un equipo nuevo de reemplazo');
                    return false;
                }

                // Confirmar si está reemplazando el original
                const primerCheckbox = checkboxesEquipos[0];
                const equipoOriginalDesmarcado = primerCheckbox && !primerCheckbox.checked;

                if (equipoOriginalDesmarcado && equiposNuevosConValor > 0) {
                    const confirmacion = confirm(
                        '🔄 REEMPLAZO DE EQUIPO\n\n' +
                        'Estás reemplazando el equipo original del préstamo.\n\n' +
                        '¿Deseas continuar?'
                    );

                    if (!confirmacion) {
                        e.preventDefault();
                        return false;
                    }
                }
            });

            // Verificación inicial
            verificarYActualizarEstado();
        });

        function mostrarAgregarEquipo() {
            document.getElementById('nuevosEquiposContainer').style.display = 'block';
            agregarNuevoEquipo();
        }

        function agregarNuevoEquipo() {
            equipoNuevoCounter++;
            const container = document.getElementById('contenedorNuevosEquipos');
            const rowId = `nuevo-equipo-${equipoNuevoCounter}`;

            const html = `
                        <div class="card nuevo-equipo-card border-2 mb-3" id="${rowId}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success">
                                        <i class="fas fa-plus me-1"></i> Nuevo Equipo
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarNuevoEquipo('${rowId}', ${equipoNuevoCounter})">
                                        <i class="fas fa-times"></i> Quitar
                                    </button>
                                </div>

                                <div class="position-relative">
                                    <input type="text" id="buscarNuevo_${equipoNuevoCounter}" class="form-control"
                                        placeholder="Buscar equipo de reemplazo..." autocomplete="off">
                                    <input type="hidden" name="equipos_nuevos[]" id="equipoNuevo_${equipoNuevoCounter}" value="">

                                    <div id="resultadosNuevo_${equipoNuevoCounter}" class="search-results"></div>

                                    <div id="seleccionadoNuevo_${equipoNuevoCounter}" class="mt-2" style="display: none;">
                                        <div class="alert alert-success mb-0">
                                            <strong id="nombreNuevo_${equipoNuevoCounter}"></strong><br>
                                            <small id="detalleNuevo_${equipoNuevoCounter}"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

            container.insertAdjacentHTML('beforeend', html);
            inicializarBusquedaNuevo(equipoNuevoCounter);
        }

        function eliminarNuevoEquipo(rowId, counterId) {
            // Limpiar el input hidden antes de eliminar
            const inputHidden = document.getElementById(`equipoNuevo_${counterId}`);
            if (inputHidden) {
                inputHidden.value = '';
            }

            document.getElementById(rowId).remove();

            if (document.querySelectorAll('#contenedorNuevosEquipos .nuevo-equipo-card').length === 0) {
                document.getElementById('nuevosEquiposContainer').style.display = 'none';
            }

            // Actualizar estado después de eliminar
            if (window.verificarYActualizarEstado) {
                window.verificarYActualizarEstado();
            }
        }

        function inicializarBusquedaNuevo(id) {
            const input = document.getElementById(`buscarNuevo_${id}`);
            const resultadosDiv = document.getElementById(`resultadosNuevo_${id}`);

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
                                resultadosDiv.innerHTML = '<div class="p-3 text-center text-muted">No se encontraron equipos</div>';
                                return;
                            }

                            // Obtener IDs de equipos actuales marcados
                            const idsActuales = Array.from(document.querySelectorAll('.equipo-checkbox-edit:checked')).map(cb => cb.value);

                            // Obtener IDs de equipos nuevos agregados
                            const idsNuevos = Array.from(document.querySelectorAll('input[name="equipos_nuevos[]"]'))
                                .map(input => input.value)
                                .filter(v => v);

                            const todosIds = [...idsActuales, ...idsNuevos];

                            // ===================================================================
                            // FILTRADO INTELIGENTE: Individual vs Por Cantidad
                            // ===================================================================
                            const filteredData = data.filter(eq => {
                                if (eq.es_individual) {
                                    // LAPTOP: No permitir duplicar (bloquear por ID)
                                    return !todosIds.includes(eq.id.toString());
                                } else {
                                    // EQUIPOS POR CANTIDAD: Siempre mostrar si hay stock
                                    // Esto permite agregar múltiples unidades del mismo tipo
                                    return eq.cantidad_disponible > 0;
                                }
                            });

                            if (filteredData.length === 0) {
                                resultadosDiv.innerHTML = '<div class="p-3 text-center text-muted">No hay equipos disponibles o todos ya seleccionados</div>';
                                return;
                            }

                            let html = '';
                            filteredData.forEach(equipo => {
                                // Escapar comillas
                                const equipoJSON = JSON.stringify(equipo).replace(/'/g, '&#39;');

                                // Construir texto de visualización
                                let displayText = '';
                                if (equipo.es_individual) {
                                    // LAPTOP: Mostrar solo nombre
                                    displayText = `<strong>${equipo.nombre_equipo}</strong><br>
                                          <small>${equipo.tipo} ${equipo.marca} - Modelo ${equipo.modelo || 'N/A'}</small>`;
                                } else {
                                    // OTROS: Mostrar con cantidad disponible
                                    displayText = `<strong>${equipo.nombre_equipo}</strong><br>
                                          <small>${equipo.tipo} ${equipo.marca} - Modelo ${equipo.modelo || 'N/A'}</small><br>
                                          <span class="badge bg-info mt-1">Disponibles: ${equipo.cantidad_disponible}/${equipo.cantidad_total}</span>`;
                                }

                                html += `
                            <div class="search-result-item" onclick='seleccionarNuevoEquipo(${equipoJSON}, ${id})'>
                                ${displayText}
                            </div>
                        `;
                            });
                            resultadosDiv.innerHTML = html;
                        })
                        .catch(err => {
                            console.error('Error en búsqueda:', err);
                            resultadosDiv.innerHTML = '<div class="p-3 text-center text-danger">Error de búsqueda</div>';
                        });
                }, 300);
            });
        }

        function seleccionarNuevoEquipo(equipo, id) {
            // CRÍTICO: Guardar el ID en el input hidden
            const inputHidden = document.getElementById(`equipoNuevo_${id}`);
            inputHidden.value = equipo.id;

            console.log(`Equipo ${equipo.id} guardado en equipoNuevo_${id}`);

            // Deshabilitar búsqueda
            const inputBusqueda = document.getElementById(`buscarNuevo_${id}`);
            inputBusqueda.value = '';
            inputBusqueda.disabled = true;

            // Ocultar resultados
            document.getElementById(`resultadosNuevo_${id}`).classList.remove('show');

            // Construir texto de visualización
            let displayText = '';
            if (equipo.es_individual) {
                // LAPTOP
                displayText = `<strong>${equipo.nombre_equipo}</strong><br>
                          <small>${equipo.tipo} ${equipo.marca} - Modelo ${equipo.modelo || 'N/A'}</small>`;
            } else {
                // OTROS: Con cantidad disponible
                displayText = `<strong>${equipo.nombre_equipo}</strong><br>
                          <small>${equipo.tipo} ${equipo.marca} - Modelo ${equipo.modelo || 'N/A'}</small><br>
                          <span class="badge bg-info mt-1">Disponible: ${equipo.cantidad_disponible}/${equipo.cantidad_total}</span>`;
            }

            // Mostrar equipo seleccionado
            document.getElementById(`nombreNuevo_${id}`).innerHTML = displayText;
            document.getElementById(`detalleNuevo_${id}`).style.display = 'none'; // Ya no se necesita
            document.getElementById(`seleccionadoNuevo_${id}`).style.display = 'block';

            // CRÍTICO: Actualizar estado después de seleccionar
            setTimeout(() => {
                if (window.verificarYActualizarEstado) {
                    window.verificarYActualizarEstado();
                }
            }, 100);
        }

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.position-relative')) {
                document.querySelectorAll('.search-results').forEach(el => el.classList.remove('show'));
            }
        });
    </script>
@endsection