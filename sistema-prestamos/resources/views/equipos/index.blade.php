@extends('layouts.panel')

@section('titulo', 'Inventario de Equipos')

@section('contenido')
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-secondary">
                <i class="fas fa-laptop me-2"></i>Listado de Equipos
            </h5>
            @if(auth()->user()->rol === 'admin')
                <a href="{{ route('equipos.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Nuevo Equipo
                </a>
            @endif
        </div>

        <div class="card-body">

            <form action="{{ route('equipos.index') }}" method="GET">
                <div class="row mb-3 g-2">

                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Buscar serie, marca..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <select name="tipo" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos los tipos</option>
                            <option value="Laptop" {{ request('tipo') == 'Laptop' ? 'selected' : '' }}>Laptops</option>
                            <option value="Mouse" {{ request('tipo') == 'Mouse' ? 'selected' : '' }}>Mouse</option>
                            <option value="Cable de red" {{ request('tipo') == 'Cable de red' ? 'selected' : '' }}>Cables de
                                red</option>
                            <option value="Cable HDMI" {{ request('tipo') == 'Cable HDMI' ? 'selected' : '' }}>Cables HDMI
                            </option>
                            <option value="Otro" {{ request('tipo') == 'Otro' ? 'selected' : '' }}>Otros</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="estado" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <option value="disponible" {{ request('estado') == 'disponible' ? 'selected' : '' }}>🟢 Disponible
                            </option>
                            <option value="prestado" {{ request('estado') == 'prestado' ? 'selected' : '' }}>🟡 Prestado
                            </option>
                            <option value="mantenimiento" {{ request('estado') == 'mantenimiento' ? 'selected' : '' }}>🔵
                                Mantenimiento</option>
                            <option value="baja" {{ request('estado') == 'baja' ? 'selected' : '' }}>🔴 De Baja</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-1">
                        <button class="btn btn-outline-primary w-100" type="submit">
                            <i class="fas fa-filter"></i>
                        </button>

                        @if(request('search') || request('estado') || request('tipo'))
                            <a href="{{ route('equipos.index') }}" class="btn btn-outline-danger w-100" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>

                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Marca / Modelo</th>
                            <th>Características</th>
                            <th class="text-center">Estado</th>
                            @if(auth()->user()->rol === 'admin')
                                <th class="text-end">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($equipos as $equipo)
                            <tr>
                                {{-- COLUMNA: CÓDIGO --}}
                                <td class="fw-bold text-primary">
                                    @if($equipo->es_individual)
                                        {{-- LAPTOP: Mostrar nombre único --}}
                                        {{ $equipo->nombre_equipo }}
                                    @else
                                        {{-- OTROS: Mostrar tipo + cantidad --}}
                                        {{ $equipo->nombre_equipo }}
                                        <span class="badge bg-info ms-2">
                                            Cantidad: {{ $equipo->cantidad_disponible }}/{{ $equipo->cantidad_total }}
                                        </span>
                                    @endif
                                </td>

                                {{-- COLUMNA: TIPO --}}
                                <td>
                                    {{-- ICONOS POR TIPO --}}
                                    @if($equipo->tipo == 'Laptop')
                                        <i class="fas fa-laptop me-1 text-secondary"></i>
                                    @elseif($equipo->tipo == 'Mouse')
                                        <i class="fas fa-mouse me-1 text-secondary"></i>
                                    @elseif($equipo->tipo == 'Cable de red' || $equipo->tipo == 'Cable HDMI')
                                        <i class="fas fa-plug me-1 text-secondary"></i>
                                    @else
                                        <i class="fas fa-box me-1 text-secondary"></i>
                                    @endif

                                    {{-- MOSTRAR TIPO (con tipo_personalizado si es "Otro") --}}
                                    @if($equipo->tipo === 'Otro' && $equipo->tipo_personalizado)
                                        <span class="badge bg-secondary">
                                            {{ $equipo->tipo }} ({{ $equipo->tipo_personalizado }})
                                        </span>
                                    @else
                                        {{ $equipo->tipo }}
                                    @endif
                                </td>

                                {{-- COLUMNA: MARCA / MODELO --}}
                                <td>{{ $equipo->marca }} - {{ $equipo->modelo }}</td>

                                {{-- COLUMNA: CARACTERÍSTICAS --}}
                                <td class="text-muted small text-truncate" style="max-width: 200px;">
                                    {{ $equipo->caracteristicas ?? 'Sin detalles' }}
                                </td>

                                {{-- COLUMNA: ESTADO --}}
                                <td class="text-center">
                                    @if($equipo->estado == 'disponible')
                                        <span class="badge bg-success rounded-pill px-3">Disponible</span>
                                    @elseif($equipo->estado == 'prestado')
                                        <span class="badge bg-warning text-dark rounded-pill px-3">Prestado</span>
                                    @elseif($equipo->estado == 'mantenimiento')
                                        <span class="badge bg-info text-dark rounded-pill px-3">Mantenimiento</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3">De Baja</span>
                                    @endif
                                </td>

                                {{-- COLUMNA: ACCIONES --}}
                                @if(auth()->user()->rol === 'admin')
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('equipos.edit', $equipo) }}" class="btn btn-sm btn-outline-warning"
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('¿Estás seguro de eliminar este equipo?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="confirmarEliminacion({{ $equipo->id }}, '{{ addslashes($equipo->nombre_equipo) }}', '{{ addslashes($equipo->tipo) }}', '{{ addslashes($equipo->marca) }}', {{ $equipo->prestamos()->exists() ? 'true' : 'false' }}, '{{ $equipo->estado }}', {{ $equipo->es_individual ? 'true' : 'false' }}, {{ $equipo->cantidad_total ?? 0 }})"
                                                    title="Eliminar o dar de baja">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->rol === 'admin' ? 6 : 5 }}" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="fas fa-box-open fa-3x"></i></div>
                                    <h6 class="text-muted">No se encontraron equipos con ese criterio.</h6>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $equipos->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            function confirmarEliminacion(id, nombre, tipo, marca, tienePrestamos, estadoActual, esIndividual, cantidadTotal) {
            const nombreCompleto = `${nombre} - ${tipo} ${marca}`;

            // ===================================================================
            // VALIDACIÓN: Si ya está dado de baja, no permitir acción
            // ===================================================================
            if (estadoActual === 'baja') { // Changed from 'dado_de_baja' to 'baja' to match the option value
                alert(
                    '⚠️ EQUIPO YA DADO DE BAJA\n\n' +
                    `📦 ${nombreCompleto}\n\n` +
                    'Este equipo ya está marcado como "Dado de Baja".\n\n' +
                    '💡 Si deseas cambiar su estado (por ejemplo, volver a ponerlo disponible), ' +
                    'puedes hacerlo usando el botón de EDITAR (✏️) y modificando el campo "Estado".'
                );
                return; // No continuar con la eliminación/baja
            }

            let titulo, mensaje, textoBoton, icono;

            if (tienePrestamos) {
                // ===================================================================
                // CASO: TIENE PRÉSTAMOS → DAR DE BAJA
                // ===================================================================
                titulo = '⚠️ Dar de Baja Equipo';
                
                if (esIndividual) {
                    // Laptop
                    mensaje = `Este equipo tiene registros de préstamos, por lo que NO se puede eliminar del sistema.\n\n` +
                        `📦 ${nombreCompleto}\n\n` +
                        `En su lugar, se marcará como "DADO DE BAJA" y dejará de estar disponible.\n\n` +
                        `¿Deseas continuar?`;
                } else {
                    // Equipo por cantidad
                    mensaje = `Este tipo de equipo tiene registros de préstamos, por lo que NO se puede eliminar del sistema.\n\n` +
                        `📦 ${tipo} (${cantidadTotal} unidades)\n\n` +
                        `En su lugar, se marcará como "DADO DE BAJA" y dejará de estar disponible.\n\n` +
                        `¿Deseas continuar?`;
                }
                
                textoBoton = 'Sí, dar de baja';
                icono = 'warning';
            } else {
                // ===================================================================
                // CASO: NO TIENE PRÉSTAMOS → ELIMINAR
                // ===================================================================
                titulo = '🗑️ Eliminar Equipo';
                
                if (esIndividual) {
                    // Laptop
                    mensaje = `Este equipo NO tiene préstamos registrados, por lo que puede ser eliminado del sistema.\n\n` +
                        `📦 ${nombreCompleto}\n\n` +
                        `⚠️ ESTA ACCIÓN NO SE PUEDE DESHACER.\n\n` +
                        `¿Estás seguro de que deseas eliminarlo permanentemente?`;
                } else {
                    // Equipo por cantidad
                    mensaje = `Este es un EQUIPO POR CANTIDAD sin préstamos registrados.\n\n` +
                        `📦 Tipo: ${tipo}\n` +
                        `📊 Cantidad: ${cantidadTotal} unidad(es)\n\n` +
                        `Se eliminará permanentemente el tipo "${tipo}" con todas sus unidades del sistema.\n\n` +
                        `⚠️ ESTA ACCIÓN NO SE PUEDE DESHACER.\n\n` +
                        `¿Estás seguro de que deseas eliminarlo?`;
                }
                
                textoBoton = 'Sí, eliminar';
                icono = 'error';
            }

            if (confirm(`${titulo}\n\n${mensaje}`)) {
                // Crear formulario dinámico y enviarlo
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/equipos/${id}`;

                // Token CSRF
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                // Method DELETE
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                // Agregar al body y enviar
                document.body.appendChild(form);
                form.submit();
            }
        }
        </script>
    @endpush
@endsection