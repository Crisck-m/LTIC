@extends('layouts.panel')

@section('titulo', 'Editar Estudiante')

@section('contenido')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-warning text-dark">
                            <i class="fas fa-user-edit me-2"></i>Editar Información
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('estudiantes.update', $estudiante) }}" method="POST">
                            @csrf
                            @method('PUT')

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row g-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Datos Personales</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Cédula de Identidad <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-id-card"></i></span>
                                        <input type="text" name="cedula"
                                            class="form-control @error('cedula') is-invalid @enderror"
                                            value="{{ old('cedula', $estudiante->cedula) }}" placeholder="Ej: 1712345678"
                                            maxlength="10" required>
                                    </div>
                                    @error('cedula')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Ingrese exactamente 10 dígitos</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Rol <span class="text-danger">*</span></label>
                                    <select name="tipo" class="form-select" required>
                                        <option value="estudiante" {{ $estudiante->tipo == 'estudiante' ? 'selected' : '' }}>
                                            Estudiante Regular</option>
                                        <option value="practicante" {{ $estudiante->tipo == 'practicante' ? 'selected' : '' }}>Practicante</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" class="form-control"
                                        value="{{ old('nombre', $estudiante->nombre) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" name="apellido" class="form-control"
                                        value="{{ old('apellido', $estudiante->apellido) }}" required>
                                </div>

                                <div class="col-12 mt-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Información Académica</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Correo Institucional <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email', $estudiante->email) }}" required>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Teléfono</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                        <input type="text" name="telefono" class="form-control"
                                            value="{{ old('telefono', $estudiante->telefono) }}">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Carrera <span class="text-danger">*</span></label>
                                    <select name="carrera" id="carrera" class="form-select" required
                                        onchange="toggleOtraCarrera()">
                                        <option value="Ingeniería en Sistemas" {{ old('carrera', $estudiante->carrera) == 'Ingeniería en Sistemas' ? 'selected' : '' }}>Ingeniería
                                            en Sistemas</option>
                                        <option value="Ingeniería Informática" {{ old('carrera', $estudiante->carrera) == 'Ingeniería Informática' ? 'selected' : '' }}>Ingeniería
                                            Informática</option>
                                        <option value="Tecnologías de la Información" {{ old('carrera', $estudiante->carrera) == 'Tecnologías de la Información' ? 'selected' : '' }}>
                                            Tecnologías de la Información</option>
                                        <option value="Otra" {{ !in_array(old('carrera', $estudiante->carrera), ['Ingeniería en Sistemas', 'Ingeniería Informática', 'Tecnologías de la Información']) ? 'selected' : '' }}>Otra</option>
                                    </select>
                                </div>

                                <div class="col-md-12" id="otraCarreraDiv"
                                    style="display: {{ !in_array(old('carrera', $estudiante->carrera), ['Ingeniería en Sistemas', 'Ingeniería Informática', 'Tecnologías de la Información']) ? 'block' : 'none' }};">
                                    <label for="otra_carrera" class="form-label fw-bold">Especificar Carrera <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="otra_carrera" id="otra_carrera"
                                        class="form-control @error('otra_carrera') is-invalid @enderror"
                                        value="{{ !in_array(old('carrera', $estudiante->carrera), ['Ingeniería en Sistemas', 'Ingeniería Informática', 'Tecnologías de la Información']) ? old('otra_carrera', $estudiante->carrera) : old('otra_carrera') }}"
                                        {{ !in_array(old('carrera', $estudiante->carrera), ['Ingeniería en Sistemas', 'Ingeniería Informática', 'Tecnologías de la Información']) ? 'required' : '' }}>
                                    @error('otra_carrera')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Observaciones</label>
                                    <textarea name="observaciones" rows="3"
                                        class="form-control">{{ old('observaciones', $estudiante->observaciones) }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning px-4">
                                    <i class="fas fa-save me-2"></i>Actualizar Estudiante
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleOtraCarrera() {
            const select = document.getElementById('carrera');
            const div = document.getElementById('otraCarreraDiv');
            const input = document.getElementById('otra_carrera');
            if (select.value === 'Otra') {
                div.style.display = 'block';
                input.required = true;
            } else {
                div.style.display = 'none';
                input.required = false;
                input.value = '';
            }
        }
    </script>
@endsection