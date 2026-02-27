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

                                            <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" class="d-inline">
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
        {{-- Modal de confirmación de eliminación/baja --}}
        <div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" id="modalEliminarHeader">
                        <h5 class="modal-title" id="modalEliminarLabel">Confirmar Acción</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="modalEliminarBody">
                        <!-- Contenido dinámico -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn" id="btnConfirmarEliminar">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let _equipoIdPendiente = null;

            function confirmarEliminacion(id, nombre, tipo, marca, tienePrestamos, estadoActual, esIndividual, cantidadTotal) {
                const nombreCompleto = `${nombre} - ${tipo} ${marca}`;
                const header = document.getElementById('modalEliminarHeader');
                const body = document.getElementById('modalEliminarBody');
                const btnConf = document.getElementById('btnConfirmarEliminar');

                // Equipo ya dado de baja → solo informar
                if (estadoActual === 'baja') {
                    header.className = 'modal-header bg-secondary bg-opacity-10';
                    document.getElementById('modalEliminarLabel').textContent = 'Equipo Ya Dado de Baja';
                    body.innerHTML = `
                                                    <div class="alert alert-secondary mb-0">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        <strong>${nombreCompleto}</strong> ya está marcado como <strong>"Dado de Baja"</strong>.
                                                    </div>
                                                    <p class="mt-3 mb-0 text-muted small">Para cambiar su estado, usa el botón
                                                        <i class="fas fa-edit"></i> <strong>Editar</strong> y modifica el campo "Estado".</p>`;
                    btnConf.style.display = 'none';
                    new bootstrap.Modal(document.getElementById('modalConfirmarEliminar')).show();
                    return;
                }

                btnConf.style.display = '';
                _equipoIdPendiente = id;

                if (tienePrestamos) {
                    // DAR DE BAJA
                    header.className = 'modal-header bg-warning bg-opacity-10';
                    document.getElementById('modalEliminarLabel').innerHTML =
                        '<i class="fas fa-arrow-down me-2 text-warning"></i>Dar de Baja Equipo';
                    const desc = esIndividual
                        ? `El equipo <strong>${nombreCompleto}</strong> tiene registros de préstamos y <strong>no puede eliminarse</strong>.`
                        : `El tipo de equipo <strong>${tipo}</strong> (${cantidadTotal} unidades) tiene registros de préstamos y <strong>no puede eliminarse</strong>.`;
                    body.innerHTML = `
                                                    <p>${desc}</p>
                                                    <p class="mb-0">Se marcará como <strong>"Dado de Baja"</strong> y dejará de estar disponible para préstamos.</p>
                                                    <p class="text-warning mt-2 mb-0"><i class="fas fa-exclamation-triangle me-1"></i><strong>¿Deseas continuar?</strong></p>`;
                    btnConf.className = 'btn btn-warning';
                    btnConf.innerHTML = '<i class="fas fa-arrow-down me-1"></i>Sí, dar de baja';
                } else {
                    // ELIMINAR
                    header.className = 'modal-header bg-danger bg-opacity-10';
                    document.getElementById('modalEliminarLabel').innerHTML =
                        '<i class="fas fa-trash me-2 text-danger"></i>Eliminar Equipo';
                    const desc = esIndividual
                        ? `El equipo <strong>${nombreCompleto}</strong> no tiene préstamos y puede ser eliminado permanentemente.`
                        : `El tipo <strong>${tipo}</strong> (${cantidadTotal} unidades) no tiene préstamos y puede ser eliminado.`;
                    body.innerHTML = `
                                                    <p>${desc}</p>
                                                    <div class="alert alert-danger py-2 mb-0">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        <strong>Esta acción no se puede deshacer.</strong>
                                                    </div>`;
                    btnConf.className = 'btn btn-danger';
                    btnConf.innerHTML = '<i class="fas fa-trash me-1"></i>Sí, eliminar';
                }

                new bootstrap.Modal(document.getElementById('modalConfirmarEliminar')).show();
            }

            document.getElementById('btnConfirmarEliminar').addEventListener('click', function () {
                if (!_equipoIdPendiente) return;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/equipos/${_equipoIdPendiente}`;

                const csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                const method = document.createElement('input');
                method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE';
                form.appendChild(method);

                document.body.appendChild(form);
                form.submit();
            });
        </script>
    @endpush
@endsection