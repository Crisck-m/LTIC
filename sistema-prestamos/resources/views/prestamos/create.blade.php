@extends('layouts.panel')

@section('titulo', 'Nuevo Préstamo')

@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-secondary">
                        <i class="fas fa-hand-holding me-2"></i>Registrar Salida de Equipo
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('prestamos.store') }}" method="POST">
                        @csrf 

                        <div class="row g-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Datos del Préstamo</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Estudiante Solicitante <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user-graduate"></i></span>
                                    <select name="estudiante_id" class="form-select" required>
                                        <option value="" disabled selected>Seleccione un estudiante...</option>
                                        @foreach($estudiantes as $estudiante)
                                            <option value="{{ $estudiante->id }}">
                                                {{ $estudiante->nombre }} {{ $estudiante->apellido }} ({{ $estudiante->carrera }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Equipo a Entregar <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-laptop"></i></span>
                                    <select name="equipo_id" class="form-select" required>
                                        <option value="" disabled selected>Seleccione un equipo disponible...</option>
                                        @foreach($equipos as $equipo)
                                            <option value="{{ $equipo->id }}">
                                                {{ $equipo->tipo }} - {{ $equipo->marca }} [Serie: {{ $equipo->codigo_puce }}]
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($equipos->isEmpty())
                                    <div class="form-text text-danger mt-1">
                                        <i class="fas fa-exclamation-circle"></i> No hay equipos disponibles en el inventario.
                                    </div>
                                @else
                                    <div class="form-text text-success mt-1">
                                        <i class="fas fa-check-circle"></i> Solo se muestran equipos con estado "Disponible".
                                    </div>
                                @endif
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Detalles y Observaciones</h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Registrado por</label>
                                <input type="text" class="form-control bg-light" value="{{ Auth::user()->name }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Fecha y Hora</label>
                                <input type="text" class="form-control bg-light" value="{{ now()->format('d/m/Y h:i A') }}" readonly>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Estado de entrega / Observaciones</label>
                                <textarea name="observaciones" rows="2" class="form-control" placeholder="Ej: Se entrega con cargador original, sin mouse..."></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('prestamos.index') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-4" {{ $equipos->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-save me-2"></i>Confirmar Préstamo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection