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

                        <div class="row g-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Datos Personales</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Cédula de Identidad</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-id-card"></i></span>
                                    <input type="text" name="matricula" class="form-control" value="{{ $estudiante->matricula }}" placeholder="Ej: 1712345678" maxlength="10" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rol en LTIC</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="estudiante" {{ $estudiante->tipo == 'estudiante' ? 'selected' : '' }}>Estudiante Regular</option>
                                    <option value="practicante" {{ $estudiante->tipo == 'practicante' ? 'selected' : '' }}>Practicante</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nombres</label>
                                <input type="text" name="nombre" class="form-control" value="{{ $estudiante->nombre }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Apellidos</label>
                                <input type="text" name="apellido" class="form-control" value="{{ $estudiante->apellido }}" required>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Información Académica</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Correo Institucional</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" value="{{ $estudiante->email }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="telefono" class="form-control" value="{{ $estudiante->telefono }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Carrera</label>
                                <select name="carrera" id="carrera" class="form-select" required onchange="toggleOtraCarrera()">
                                    <option value="Ingeniería en Sistemas" {{ $estudiante->carrera == 'Ingeniería en Sistemas' ? 'selected' : '' }}>Ingeniería en Sistemas</option>
                                    <option value="Ingeniería Informática" {{ $estudiante->carrera == 'Ingeniería Informática' ? 'selected' : '' }}>Ingeniería Informática</option>
                                    <option value="Tecnologías de la Información" {{ $estudiante->carrera == 'Tecnologías de la Información' ? 'selected' : '' }}>Tecnologías de la Información</option>
                                    <option value="Otra" {{ !in_array($estudiante->carrera, ['Ingeniería en Sistemas', 'Ingeniería Informática', 'Tecnologías de la Información']) ? 'selected' : '' }}>Otra</option>
                                </select>
                            </div>

                            <div class="col-md-12" id="otraCarreraDiv" style="display: {{ !in_array($estudiante->carrera, ['Ingeniería en Sistemas', 'Ingeniería Informática', 'Tecnologías de la Información']) ? 'block' : 'none' }};">
                                <label for="otra_carrera" class="form-label fw-bold">Especificar Carrera <span class="text-danger">*</span></label>
                                <input type="text" name="otra_carrera" id="otra_carrera" class="form-control" value="{{ !in_array($estudiante->carrera, ['Ingeniería en Sistemas', 'Ingeniería Informática', 'Tecnologías de la Información']) ? $estudiante->carrera : '' }}" {{ !in_array($estudiante->carrera, ['Ingeniería en Sistemas', 'Ingeniería Informática', 'Tecnologías de la Información']) ? 'required' : '' }}>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Observaciones</label>
                                <textarea name="observaciones" rows="3" class="form-control">{{ $estudiante->observaciones }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-warning px-4">Actualizar Estudiante</button>
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