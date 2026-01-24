@extends('layouts.panel')

@section('titulo', 'Registrar Equipo')

@section('contenido')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-secondary">
                            <i class="fas fa-laptop-medical me-2"></i>Datos del Nuevo Equipo
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('equipos.store') }}" method="POST">
                            @csrf

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Error al registrar
                                        el equipo</h6>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="row g-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Identificación</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nombre / Código Activo / Serie <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="codigo_puce"
                                        class="form-control @error('codigo_puce') is-invalid @enderror"
                                        placeholder="Ej: LAP-001" value="{{ old('codigo_puce') }}" required>
                                    @error('codigo_puce')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Tipo de Equipo <span
                                            class="text-danger">*</span></label>
                                    <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                                        <option value="Laptop" {{ old('tipo') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                                        <option value="Proyector" {{ old('tipo') == 'Proyector' ? 'selected' : '' }}>Proyector
                                        </option>
                                        <option value="Tablet" {{ old('tipo') == 'Tablet' ? 'selected' : '' }}>Tablet</option>
                                        <option value="Accesorio" {{ old('tipo') == 'Accesorio' ? 'selected' : '' }}>Accesorio
                                            (Cargador, Mouse...)</option>
                                        <option value="Otro" {{ old('tipo') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                    @error('tipo')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mt-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Detalles Técnicos</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Marca <span class="text-danger">*</span></label>
                                    <input type="text" name="marca"
                                        class="form-control @error('marca') is-invalid @enderror"
                                        placeholder="Ej: HP, Dell, Epson" value="{{ old('marca') }}" required>
                                    @error('marca')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Modelo <span class="text-danger">*</span></label>
                                    <input type="text" name="modelo"
                                        class="form-control @error('modelo') is-invalid @enderror"
                                        placeholder="Ej: Pavilion 15" value="{{ old('modelo') }}" required>
                                    @error('modelo')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Estado Inicial</label>
                                    <select name="estado" class="form-select @error('estado') is-invalid @enderror">
                                        <option value="disponible" {{ old('estado', 'disponible') == 'disponible' ? 'selected' : '' }}>🟢 Disponible</option>
                                        <option value="mantenimiento" {{ old('estado') == 'mantenimiento' ? 'selected' : '' }}>🔵 En Mantenimiento</option>
                                        <option value="baja" {{ old('estado') == 'baja' ? 'selected' : '' }}>🔴 De Baja
                                            (Dañado)</option>
                                    </select>
                                    @error('estado')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Características (Procesador, RAM, Detalles)</label>
                                    <textarea name="caracteristicas" rows="3"
                                        class="form-control @error('caracteristicas') is-invalid @enderror"
                                        placeholder="Ej: Core i7, 16GB RAM, SSD 512GB">{{ old('caracteristicas') }}</textarea>
                                    @error('caracteristicas')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('equipos.index') }}" class="btn btn-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Guardar Equipo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection