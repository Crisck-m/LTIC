@extends('layouts.app')

@section('title', 'Editar Estudiante')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title">
                        <i class="fas fa-edit text-warning"></i>
                        Editar Estudiante: {{ $estudiante->nombre_completo }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('estudiantes.update', $estudiante) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="matricula" class="form-label">
                                        Matrícula <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('matricula') is-invalid @enderror" 
                                           id="matricula" name="matricula" 
                                           value="{{ old('matricula', $estudiante->matricula) }}" 
                                           placeholder="Ej: 20240001" required>
                                    @error('matricula')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">
                                        Tipo <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('tipo') is-invalid @enderror" 
                                            id="tipo" name="tipo" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="estudiante" {{ old('tipo', $estudiante->tipo) == 'estudiante' ? 'selected' : '' }}>
                                            Estudiante
                                        </option>
                                        <option value="pasante" {{ old('tipo', $estudiante->tipo) == 'pasante' ? 'selected' : '' }}>
                                            Pasante
                                        </option>
                                    </select>
                                    @error('tipo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">
                                        Nombre <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                           id="nombre" name="nombre" 
                                           value="{{ old('nombre', $estudiante->nombre) }}" 
                                           placeholder="Nombre del estudiante" required>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="apellido" class="form-label">
                                        Apellido <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('apellido') is-invalid @enderror" 
                                           id="apellido" name="apellido" 
                                           value="{{ old('apellido', $estudiante->apellido) }}" 
                                           placeholder="Apellido del estudiante" required>
                                    @error('apellido')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" 
                                           value="{{ old('email', $estudiante->email) }}" 
                                           placeholder="ejemplo@dominio.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" name="telefono" 
                                           value="{{ old('telefono', $estudiante->telefono) }}" 
                                           placeholder="+52 123 456 7890">
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="carrera" class="form-label">
                                        Carrera <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('carrera') is-invalid @enderror" 
                                            id="carrera" name="carrera" required>
                                        <option value="">Seleccionar carrera</option>
                                        <option value="Ingeniería en Sistemas" {{ old('carrera', $estudiante->carrera) == 'Ingeniería en Sistemas' ? 'selected' : '' }}>
                                            Ingeniería en Sistemas
                                        </option>
                                        <option value="Ingeniería Informática" {{ old('carrera', $estudiante->carrera) == 'Ingeniería Informática' ? 'selected' : '' }}>
                                            Ingeniería Informática
                                        </option>
                                        <option value="Tecnologías de la Información" {{ old('carrera', $estudiante->carrera) == 'Tecnologías de la Información' ? 'selected' : '' }}>
                                            Tecnologías de la Información
                                        </option>
                                        <option value="Otra" {{ old('carrera', $estudiante->carrera) == 'Otra' ? 'selected' : '' }}>
                                            Otra
                                        </option>
                                    </select>
                                    @error('carrera')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" 
                                               id="activo" name="activo" value="1" 
                                               {{ old('activo', $estudiante->activo) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="activo">
                                            Estudiante activo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                              id="observaciones" name="observaciones" rows="3" 
                                              placeholder="Observaciones adicionales...">{{ old('observaciones', $estudiante->observaciones) }}</textarea>
                                    @error('observaciones')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Actualizar Estudiante
                                </button>
                                <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection