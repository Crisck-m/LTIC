@extends('layouts.panel')

@section('titulo', 'Registrar Estudiante')

@section('contenido')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-secondary">
                            <i class="fas fa-user-plus me-2"></i>Nuevo Estudiante
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('estudiantes.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <!-- Cédula -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Cédula <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-id-card"></i></span>
                                        <input type="text" name="cedula"
                                            class="form-control @error('cedula') is-invalid @enderror"
                                            value="{{ old('cedula') }}" placeholder="Ej: 1712345678" maxlength="10"
                                            required>
                                    </div>
                                    @error('cedula')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tipo -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-user-tag"></i></span>
                                        <select name="tipo" class="form-select @error('tipo') is-invalid @enderror"
                                            required>
                                            <option value="" disabled selected>Seleccione...</option>
                                            <option value="estudiante" {{ old('tipo') == 'estudiante' ? 'selected' : '' }}>
                                                Estudiante</option>
                                            <option value="practicante" {{ old('tipo') == 'practicante' ? 'selected' : '' }}>
                                                Practicante</option>
                                        </select>
                                    </div>
                                    @error('tipo')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nombre -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                        <input type="text" name="nombre"
                                            class="form-control @error('nombre') is-invalid @enderror"
                                            value="{{ old('nombre') }}" placeholder="Nombres" required>
                                    </div>
                                    @error('nombre')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Apellido -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Apellido <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                        <input type="text" name="apellido"
                                            class="form-control @error('apellido') is-invalid @enderror"
                                            value="{{ old('apellido') }}" placeholder="Apellidos" required>
                                    </div>
                                    @error('apellido')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email') }}" placeholder="correo@puce.edu.ec" required>
                                    </div>
                                    @error('email')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Teléfono -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Teléfono</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                        <input type="text" name="telefono"
                                            class="form-control @error('telefono') is-invalid @enderror"
                                            value="{{ old('telefono') }}" placeholder="Ej: 0987654321" maxlength="10">
                                    </div>
                                    @error('telefono')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Carrera <span class="text-danger">*</span></label>
                                    <select name="carrera_id" id="carrera_id" class="form-select" required
                                        onchange="toggleCarreraOtra()">
                                        <option value="" disabled selected>Seleccione una carrera...</option>
                                        @foreach($carreras as $carrera)
                                            <option value="{{ $carrera->id }}" {{ old('carrera_id') == $carrera->id ? 'selected' : '' }}>
                                                {{ $carrera->nombre }}
                                            </option>
                                        @endforeach
                                        <option value="otra" {{ old('carrera_id') == 'otra' ? 'selected' : '' }}>Otra</option>
                                    </select>
                                </div>

                                <div class="col-md-6" id="carreraOtraContainer" style="display: none;">
                                    <label class="form-label fw-bold">Especifique la carrera <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="carrera_otra" id="carrera_otra" class="form-control"
                                        placeholder="Escriba el nombre de la carrera" value="{{ old('carrera_otra') }}">
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Esta carrera se agregará automáticamente al
                                        sistema
                                    </small>
                                </div>

                                <script>
                                    function toggleCarreraOtra() {
                                        const select = document.getElementById('carrera_id');
                                        const container = document.getElementById('carreraOtraContainer');
                                        const input = document.getElementById('carrera_otra');

                                        if (select.value === 'otra') {
                                            container.style.display = 'block';
                                            input.required = true;
                                        } else {
                                            container.style.display = 'none';
                                            input.required = false;
                                            input.value = '';
                                        }
                                    }

                                    // Ejecutar al cargar la página por si hay old()
                                    document.addEventListener('DOMContentLoaded', toggleCarreraOtra);
                                </script>

                                <!-- Campo para otra carrera (oculto por defecto) -->
                                <div class="col-md-12" id="otraCarreraDiv" style="display: none;">
                                    <label class="form-label fw-bold">Especifique la carrera <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-graduation-cap"></i></span>
                                        <input type="text" name="otra_carrera" id="otra_carrera"
                                            class="form-control @error('otra_carrera') is-invalid @enderror"
                                            value="{{ old('otra_carrera') }}" placeholder="Escriba el nombre de la carrera">
                                    </div>
                                    @error('otra_carrera')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Observaciones -->
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Observaciones</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-comment"></i></span>
                                        <textarea name="observaciones" rows="3"
                                            class="form-control @error('observaciones') is-invalid @enderror"
                                            placeholder="Notas adicionales (opcional)">{{ old('observaciones') }}</textarea>
                                    </div>
                                    @error('observaciones')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Registrar Estudiante
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
            const carreraSelect = document.getElementById('carrera');
            const otraCarreraDiv = document.getElementById('otraCarreraDiv');
            const otraCarreraInput = document.getElementById('otra_carrera');

            if (carreraSelect.value === 'Otra') {
                otraCarreraDiv.style.display = 'block';
                otraCarreraInput.required = true;
            } else {
                otraCarreraDiv.style.display = 'none';
                otraCarreraInput.required = false;
                otraCarreraInput.value = '';
            }
        }

        // Ejecutar al cargar la página para manejar el estado de old() después de errores de validación
        document.addEventListener('DOMContentLoaded', function () {
            toggleOtraCarrera();
        });
    </script>
@endsection