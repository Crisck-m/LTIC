@extends('layouts.panel')

@section('titulo', 'Editar Equipo')

@section('contenido')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-warning text-dark">
                            <i class="fas fa-tools me-2"></i>Editar Equipo: {{ $equipo->codigo_puce }}
                        </h5>
                    </div>

                    <div class="card-body p-4">
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
                                    <input type="text" name="codigo_puce" class="form-control"
                                        value="{{ $equipo->codigo_puce }}" required>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection