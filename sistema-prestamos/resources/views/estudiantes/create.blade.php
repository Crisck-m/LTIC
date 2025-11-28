@extends('layouts.app')

@section('title', 'Registrar Estudiante')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title">
                        <i class="fas fa-user-plus text-primary"></i>
                        Registrar Nuevo Estudiante
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('estudiantes.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="matricula" class="form-label">
                                        Matrícula <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('matricula') is-invalid @enderror" 
                                           id="matricula" name="matricula" value="{{ old('matricula') }}" 
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
                                        <option value="estudiante" {{ old('tipo') == 'estudiante' ? 'selected' : '' }}>
                                            Estudiante
                                        </option>
                                        <option value="pasante" {{ old('tipo') == 'pasante' ? 'selected' : '' }}>
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
                                           id="nombre" name="nombre" value="{{ old('nombre') }}" 
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
                                           id="apellido" name="apellido" value="{{ old('apellido') }}" 
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
                                           id="email" name="email" value="{{ old('email') }}" 
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
                                           id="telefono" name="telefono" value="{{ old('telefono') }}" 
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
                                        <option value="Ingeniería en Sistemas" {{ old('carrera') == 'Ingeniería en Sistemas' ? 'selected' : '' }}>
                                            Ingeniería en Sistemas
                                        </option>
                                        <option value="Ingeniería Informática" {{ old('carrera') == 'Ingeniería Informática' ? 'selected' : '' }}>
                                            Ingeniería Informática
                                        </option>
                                        <option value="Tecnologías de la Información" {{ old('carrera') == 'Tecnologías de la Información' ? 'selected' : '' }}>
                                            Tecnologías de la Información
                                        </option>
                                        <option value="Otra" {{ old('carrera') == 'Otra' ? 'selected' : '' }}>
                                            Otra
                                        </option>
                                    </select>
                                    @error('carrera')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                              id="observaciones" name="observaciones" rows="3" 
                                              placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                                    @error('observaciones')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Estudiante
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