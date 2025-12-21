@extends('layouts.panel')

@section('titulo', 'Nuevo Estudiante')

@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-secondary">
                        <i class="fas fa-user-plus me-2"></i>Información del Estudiante
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('estudiantes.store') }}" method="POST">
                        @csrf 

                        <div class="row g-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Datos Personales</h6>
                            </div>

                            <div class="col-md-6">
                                <label for="matricula" class="form-label fw-bold">Cédula de Identidad <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-id-card"></i></span>
                                    <input type="text" name="matricula" id="matricula" class="form-control" placeholder="Ej: 1712345678" maxlength="10" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="tipo" class="form-label fw-bold">Rol en LTIC <span class="text-danger">*</span></label>
                                <select name="tipo" id="tipo" class="form-select" required>
                                    <option value="estudiante">Estudiante Regular</option>
                                    <option value="practicante">Practicante</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="nombre" class="form-label fw-bold">Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="apellido" class="form-label fw-bold">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellido" id="apellido" class="form-control" required>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Información Académica y Contacto</h6>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold">Correo Institucional <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="usuario@puce.edu.ec" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="telefono" class="form-label fw-bold">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="telefono" id="telefono" class="form-control" placeholder="099...">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="carrera" class="form-label fw-bold">Carrera <span class="text-danger">*</span></label>
                                <select name="carrera" id="carrera" class="form-select" required>
                                    <option value="" disabled selected>Seleccione una carrera...</option>
                                    <option value="Ingeniería en Sistemas">Ingeniería en Sistemas</option>
                                    <option value="Ingeniería Informática">Ingeniería Informática</option>
                                    <option value="Tecnologías de la Información">Tecnologías de la Información</option>
                                    <option value="Otra">Otra</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="observaciones" class="form-label fw-bold">Observaciones (Opcional)</label>
                                <textarea name="observaciones" id="observaciones" rows="3" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Guardar Estudiante
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection