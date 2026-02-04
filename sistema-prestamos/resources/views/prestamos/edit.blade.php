@extends('layouts.panel')

@section('titulo', 'Editar Préstamo')

@section('contenido')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-secondary">
                                <i class="fas fa-edit me-2"></i>Editar Préstamo
                            </h5>
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
                        <form action="{{ route('prestamos.update', $prestamo->id) }}" method="POST" id="formPrestamo">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Datos del Préstamo (Solo Lectura)</h6>
                                </div>

                                <!-- ESTUDIANTE SOLICITANTE -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Estudiante Solicitante</label>
                                    <div class="alert alert-secondary d-flex align-items-center mb-0 p-2">
                                        <i class="fas fa-user-graduate me-3 fa-lg text-secondary"></i>
                                        <div>
                                            <div class="fw-bold">{{ $prestamo->estudiante->nombre }}
                                                {{ $prestamo->estudiante->apellido }}
                                            </div>
                                            <small>{{ $prestamo->estudiante->carrera }}</small>
                                        </div>
                                    </div>
                                    <input type="hidden" name="estudiante_id" value="{{ $prestamo->estudiante_id }}">
                                </div>

                                <!-- PRACTICANTE RESPONSABLE -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Atendido por (Practicante)</label>
                                    <div class="alert alert-secondary d-flex align-items-center mb-0 p-2">
                                        <i class="fas fa-user-tie me-3 fa-lg text-info"></i>
                                        <div>
                                            <div class="fw-bold">{{ $prestamo->practicante->nombre }}
                                                {{ $prestamo->practicante->apellido }}
                                            </div>
                                            <small>Responsable original del registro</small>
                                        </div>
                                    </div>
                                    <input type="hidden" name="practicante_id" value="{{ $prestamo->practicante_id }}">
                                </div>

                                <!-- EQUIPOS -->
                                <div class="col-12 mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="text-primary border-bottom pb-2 mb-0 w-100">
                                            <i class="fas fa-laptop me-2"></i>Equipos en Préstamo
                                        </h6>
                                    </div>
                                    <div class="d-flex justify-content-end mb-3 mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="agregarFilaEquipo()">
                                            <i class="fas fa-plus me-1"></i> Añadir equipo/accesorio
                                        </button>
                                    </div>

                                    <div id="contenedorEquipos">
                                        <!-- EQUIPO ACTUAL (PRIMERO) -->
                                        <div class="equipo-row mb-3" id="equipo-row-1">
                                            <div class="position-relative">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i
                                                            class="fas fa-laptop"></i></span>
                                                    <!-- Mostramos el equipo actual como "placeholder" pero permitimos buscar para cambiarlo -->
                                                    <input type="text" id="buscarEquipo_1" class="form-control"
                                                        placeholder="Buscar equipo/accesorio 1..." autocomplete="off">

                                                    <!-- Nota aclaratoria específica para el equipo 1 -->
                                                    <div class="form-text text-warning mt-1 ms-1">
                                                        <i class="fas fa-exclamation-circle me-1"></i>
                                                        Si cambias este equipo/accesorio, reemplazarás al equipo original
                                                        del préstamo.
                                                    </div>

                                                    <!-- Botón eliminar solo si hay más de 1 equipo -->
                                                    <button type="button" class="btn btn-outline-danger btn-remove-row"
                                                        onclick="eliminarFila('equipo-row-1')" title="Eliminar este equipo">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>

                                                <input type="hidden" name="equipo_id[]" id="equipo_id_1"
                                                    value="{{ $prestamo->equipo_id }}">

                                                <!-- Resultados -->
                                                <div id="resultadosEquipo_1" class="search-results"></div>

                                                <!-- Seleccionado Inicialmente -->
                                                <div id="equipoSeleccionado_1" class="selected-item mt-2"
                                                    style="display: block;">
                                                    <div
                                                        class="alert alert-info mb-0 d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong id="equipoNombre_1">Nombre:
                                                                {{ $prestamo->equipo->nombre_equipo }}</strong><br>
                                                            <small id="equipoDetalle_1">{{ $prestamo->equipo->tipo }}
                                                                {{ $prestamo->equipo->marca }} - Modelo
                                                                {{ $prestamo->equipo->modelo }}</small>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="limpiarEquipo(1)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-text text-success mt-1">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <span>
                                                <strong>Nota:</strong> Si eliminas el equipo original y guardas, ese
                                                préstamo específico se marcará como devuelto/cancelado.
                                                Si añades nuevos equipos, se crearán nuevos préstamos.
                                                Si cambias el equipo, se actualizará el registro.
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- FECHAS Y OBSERVACIONES -->
                                <div class="col-12 mt-3">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Detalles Adicionales (Compartidos)</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fecha Esperada de Devolución <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="date" name="fecha_devolucion_esperada" id="fecha_devolucion_esperada"
                                            class="form-control"
                                            value="{{ \Carbon\Carbon::parse($prestamo->fecha_devolucion_esperada)->format('Y-m-d') }}"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Estado de entrega / Observaciones</label>
                                    <textarea name="observaciones" rows="1" class="form-control"
                                        placeholder="Ej: Se entrega con cargador original...">{{ $prestamo->observaciones_prestamo }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('prestamos.index') }}" class="btn btn-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
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
        /* Estilos para el autocompletado (mismos que en create) */
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

        .equipo-row {
            position: relative;
            transition: all 0.3s ease;
        }
    </style>

    <script>
        let timeoutsEquipos = {};
        let equipoCounter = 1; // Empezamos en 1 porque ya tenemos el equipo 1 cargado

        // Inicializar búsqueda del equipo 1
        inicializarBusquedaEquipo(1);

        function agregarFilaEquipo() {
            equipoCounter++;
            const container = document.getElementById('contenedorEquipos');
            const rowId = `equipo-row-${equipoCounter}`;

            // Calcular visual index
            const visualIndex = document.querySelectorAll('.equipo-row').length + 1;

            const html = `
                    <div class="equipo-row mb-3" id="${rowId}">
                        <div class="position-relative">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-laptop"></i></span>
                                <input type="text" id="buscarEquipo_${equipoCounter}" class="form-control"
                                    placeholder="Buscar equipo/accesorio ${visualIndex}..." autocomplete="off">

                                <button type="button" class="btn btn-outline-danger" onclick="eliminarFila('${rowId}')" title="Eliminar fila">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <input type="hidden" name="equipo_id[]" id="equipo_id_${equipoCounter}" required>

                            <!-- Resultados -->
                            <div id="resultadosEquipo_${equipoCounter}" class="search-results"></div>

                            <!-- Seleccionado -->
                            <div id="equipoSeleccionado_${equipoCounter}" class="selected-item mt-2" style="display: none;">
                                <div class="alert alert-info mb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong id="equipoNombre_${equipoCounter}"></strong><br>
                                        <small id="equipoDetalle_${equipoCounter}"></small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="limpiarEquipo(${equipoCounter})">
                                        <i class="fas fa-times"></i>
                                    </button>
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
            verificarBotonesEliminar();
        }

        function eliminarFila(rowId) {
            const rows = document.querySelectorAll('.equipo-row');
            if (rows.length <= 1) {
                alert('Debe haber al menos un equipo en el préstamo.');
                return;
            }

            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
                actualizarNumeracionEquipos();
                verificarBotonesEliminar();
            }
        }

        function actualizarNumeracionEquipos() {
            const rows = document.querySelectorAll('.equipo-row');
            rows.forEach((row, index) => {
                const numero = index + 1;
                const input = row.querySelector('input[id^="buscarEquipo_"]');
                if (input) {
                    input.placeholder = `Buscar equipo/accesorio ${numero}...`;
                }
            });
        }

        function verificarBotonesEliminar() {
            const rows = document.querySelectorAll('.equipo-row');
            // Si solo hay 1 fila, quizás querramos ocultar el botón de eliminar o no.
            // El requerimiento dice "no quede vacío". Validamos en submit, pero visualmente ayuda.
            // Si el usuario elimina la única fila, no puede agregar otra. Mejor dejar siempre visible y validar en eliminarFila.
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
                                resultadosDiv.innerHTML = '<div class="p-3 text-center text-muted">No se encontraron equipos</div>';
                                return;
                            }

                            // Filtrar equipos ya seleccionados
                            const inputsIds = document.querySelectorAll('input[name="equipo_id[]"]');
                            const idsSeleccionados = Array.from(inputsIds).map(input => input.value).filter(val => val);

                            // Permitir mostrar el equipo actual de esta fila si estamos editando (aunque ya esté en value)
                            // pero la búsqueda es para CAMBIAR, así que mejor filtramos todo lo que esté seleccionado
                            // EXCEPTO quizás si buscamos el mismo nombre... pero bueno, filtrado estricto es más seguro.

                            const filteredData = data.filter(eq => !idsSeleccionados.includes(eq.id.toString()));

                            if (filteredData.length === 0) {
                                resultadosDiv.innerHTML = '<div class="p-3 text-center text-muted">Equipos ya seleccionados</div>';
                                return;
                            }

                            let html = '';
                            filteredData.forEach(equipo => {
                                const modelo = (equipo.modelo || '').replace(/'/g, "\\'");
                                const nombre = equipo.nombre_equipo.replace(/'/g, "\\'");
                                const marca = equipo.marca.replace(/'/g, "\\'");

                                html += `
                                        <div class="search-result-item" onclick="seleccionarEquipo(${id}, ${equipo.id}, '${equipo.tipo}', '${marca}', '${modelo}', '${nombre}')">
                                            <strong>${equipo.nombre_equipo}</strong>
                                            <small>${equipo.tipo} ${equipo.marca}</small>
                                        </div>
                                    `;
                            });
                            resultadosDiv.innerHTML = html;
                        })
                        .catch(err => {
                            resultadosDiv.innerHTML = '<div class="p-3 text-center text-danger">Error de búsqueda</div>';
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
            document.getElementById(`equipoSeleccionado_${rowId}`).style.display = 'block';
            document.getElementById(`buscarEquipo_${rowId}`).style.display = 'none';
        }

        function limpiarEquipo(rowId) {
            document.getElementById(`equipo_id_${rowId}`).value = '';
            document.getElementById(`buscarEquipo_${rowId}`).value = '';
            document.getElementById(`equipoSeleccionado_${rowId}`).style.display = 'none';
            document.getElementById(`buscarEquipo_${rowId}`).style.display = 'block';
            document.getElementById(`buscarEquipo_${rowId}`).focus();
        }

        // Validación al enviar
        document.getElementById('formPrestamo').addEventListener('submit', function (e) {
            const inputsIds = document.querySelectorAll('input[name="equipo_id[]"]');
            let count = 0;
            inputsIds.forEach(input => {
                if (input.value) count++;
            });

            if (count === 0) {
                e.preventDefault();
                alert('El préstamo no puede quedar vacío. Debe haber al menos un equipo seleccionado.');
            }
        });

        // Cerrar resultados click fuera
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.input-group') && !e.target.closest('.search-results')) {
                document.querySelectorAll('.search-results').forEach(el => el.classList.remove('show'));
            }
        });
    </script>
@endsection