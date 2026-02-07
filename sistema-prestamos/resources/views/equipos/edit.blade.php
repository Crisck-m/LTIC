@extends('layouts.panel')

@section('titulo', 'Editar Equipo')

@section('contenido')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-warning text-dark">
                            <i class="fas fa-tools me-2"></i>Editar Equipo: {{ $equipo->nombre_equipo }}
                        </h5>
                    </div>

                    <div class="card-body p-4">

                        @if($equipo->es_individual)
                            {{-- ============================================ --}}
                            {{-- FORMULARIO PARA LAPTOPS (EQUIPOS INDIVIDUALES) --}}
                            {{-- ============================================ --}}
                            <form action="{{ route('equipos.update', $equipo) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row g-4">
                                    <div class="col-12">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">Identificación</h6>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nombre / Código Activo / Serie <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="nombre_equipo" class="form-control"
                                            value="{{ $equipo->nombre_equipo }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Tipo</label>
                                        <select name="tipo" class="form-select" required>
                                            <option value="Laptop" {{ $equipo->tipo == 'Laptop' ? 'selected' : '' }}>Laptop
                                            </option>
                                            <option value="Proyector" {{ $equipo->tipo == 'Proyector' ? 'selected' : '' }}>
                                                Proyector</option>
                                            <option value="Tablet" {{ $equipo->tipo == 'Tablet' ? 'selected' : '' }}>Tablet
                                            </option>
                                            <option value="Accesorio" {{ $equipo->tipo == 'Accesorio' ? 'selected' : '' }}>
                                                Accesorio</option>
                                            <option value="Otro" {{ $equipo->tipo == 'Otro' ? 'selected' : '' }}>Otro</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">Detalles Técnicos</h6>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Marca</label>
                                        <input type="text" name="marca" class="form-control" value="{{ $equipo->marca }}"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Modelo</label>
                                        <input type="text" name="modelo" class="form-control" value="{{ $equipo->modelo }}"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Estado</label>
                                        <select name="estado" class="form-select">
                                            <option value="disponible" {{ $equipo->estado == 'disponible' ? 'selected' : '' }}>🟢
                                                Disponible</option>
                                            <option value="mantenimiento" {{ $equipo->estado == 'mantenimiento' ? 'selected' : '' }}>🔵 En Mantenimiento</option>
                                            <option value="baja" {{ $equipo->estado == 'baja' ? 'selected' : '' }}>🔴 De Baja
                                            </option>
                                            <option value="prestado" {{ $equipo->estado == 'prestado' ? 'selected' : '' }}
                                                disabled>🟡 Prestado (No editable)</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold">Características</label>
                                        <textarea name="caracteristicas" rows="3"
                                            class="form-control">{{ $equipo->caracteristicas }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <a href="{{ route('equipos.index') }}" class="btn btn-secondary px-4">Cancelar</a>
                                    <button type="submit" class="btn btn-warning px-4">Guardar Cambios</button>
                                </div>
                            </form>

                        @else
                            {{-- ============================================ --}}
                            {{-- FORMULARIO PARA EQUIPOS POR CANTIDAD --}}
                            {{-- ============================================ --}}
                            <form action="{{ route('equipos.update', $equipo) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Equipo por Cantidad:</strong> Solo puedes modificar la cantidad de existencias de
                                    este tipo de equipo.
                                </div>

                                <div class="row g-4">
                                    <div class="col-12">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">Información del Equipo</h6>
                                    </div>

                                    {{-- TIPO DE EQUIPO (Solo lectura) --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Tipo de Equipo</label>
                                        <input type="text" class="form-control bg-light"
                                            value="{{ $equipo->tipo }}{{ $equipo->tipo_personalizado ? ' (' . $equipo->tipo_personalizado . ')' : '' }}"
                                            readonly>
                                        <small class="text-muted">No se puede modificar el tipo de un equipo existente</small>
                                    </div>

                                    {{-- CANTIDAD ACTUAL --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Existencias Actuales</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-primary text-white">Total:</span>
                                            <input type="text" class="form-control bg-light text-center fw-bold"
                                                value="{{ $equipo->cantidad_total }}" readonly>
                                            <span class="input-group-text bg-success text-white">Disponibles:</span>
                                            <input type="text" class="form-control bg-light text-center fw-bold"
                                                value="{{ $equipo->cantidad_disponible }}" readonly>
                                        </div>
                                    </div>

                                    {{-- CANTIDAD PRESTADA (Si aplica) --}}
                                    @php
                                        $cantidadPrestada = $equipo->cantidad_total - $equipo->cantidad_disponible;
                                    @endphp
                                    @if($cantidadPrestada > 0)
                                        <div class="col-12">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <strong>Atención:</strong> Actualmente hay <strong>{{ $cantidadPrestada }}
                                                    unidad(es) prestada(s)</strong>.
                                                La cantidad total no puede ser menor a {{ $cantidadPrestada }}.
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">Modificar Cantidad</h6>
                                    </div>

                                    {{-- NUEVA CANTIDAD --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nueva Cantidad Total <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="cantidad_total"
                                            class="form-control @error('cantidad_total') is-invalid @enderror"
                                            value="{{ old('cantidad_total', $equipo->cantidad_total) }}"
                                            min="{{ $cantidadPrestada }}" required>
                                        <small class="text-muted">
                                            @if($cantidadPrestada > 0)
                                                Mínimo permitido: {{ $cantidadPrestada }} (unidades prestadas)
                                            @else
                                                Puedes aumentar o reducir la cantidad según necesites
                                            @endif
                                        </small>
                                        @error('cantidad_total')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- ESTADO --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Estado</label>
                                        <select name="estado" class="form-select">
                                            <option value="disponible" {{ $equipo->estado == 'disponible' ? 'selected' : '' }}>🟢
                                                Disponible</option>
                                            <option value="mantenimiento" {{ $equipo->estado == 'mantenimiento' ? 'selected' : '' }}>🔵 En Mantenimiento</option>
                                        </select>
                                        <small class="text-muted">El estado afecta si las unidades están disponibles para
                                            préstamo</small>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <a href="{{ route('equipos.index') }}" class="btn btn-secondary px-4">Cancelar</a>
                                    <button type="submit" class="btn btn-warning px-4">
                                        <i class="fas fa-save me-2"></i>Actualizar Cantidad
                                    </button>
                                </div>
                            </form>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection