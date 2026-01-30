@extends('layouts.panel')

@section('titulo', 'Confirmar Devolución')

@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning bg-opacity-10 py-3">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-exclamation-triangle me-2"></i>Verificar Devolución de Equipo
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('prestamos.devolver', $prestamo) }}" method="POST">
                        @csrf
                        @method('PUT') 

                        <div class="row g-4">
                            <div class="col-12">
                                <h6 class="text-secondary border-bottom pb-2 mb-3">Datos del Préstamo Activo</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Estudiante</label>
                                <input type="text" class="form-control bg-light" value="{{ $prestamo->estudiante->nombre }} {{ $prestamo->estudiante->apellido }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Equipo a Recibir</label>
                                <input type="text" class="form-control bg-light" value="{{ $prestamo->equipo->tipo }} - {{ $prestamo->equipo->marca }} [{{ $prestamo->equipo->codigo_puce }}]" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Fecha de Salida</label>
                                <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y h:i A') }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Observaciones Iniciales</label>
                                <input type="text" class="form-control bg-light" value="{{ $prestamo->observaciones_prestamo ?? 'Ninguna' }}" readonly>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="text-success border-bottom pb-2 mb-3">Registro de Entrada (Devolución)</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Practicante que revisa el equipo <span class="text-danger">*</span></label>
                                <select name="practicante_devolucion_id" class="form-select" required>
                                    <option value="" disabled selected>Seleccione quién recibe el equipo...</option>
                                    @foreach($practicantes as $practicante)
                                        <option value="{{ $practicante->id }}">
                                            {{ $practicante->nombre }} {{ $practicante->apellido }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">Responsable de verificar la devolución.</div>
                            </div>

                            <div class="col-md-6"></div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Observaciones de Recepción (Estado del equipo)</label>
                                <textarea name="observaciones_devolucion" rows="3" class="form-control" placeholder="Ej: Equipo devuelto en buenas condiciones, sin novedades..."></textarea>
                            </div>

                            <div class="col-md-12">
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Al confirmar, el equipo cambiará automáticamente a estado <strong>"Disponible"</strong> en el inventario.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('prestamos.index') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-check-circle me-2"></i>Confirmar Devolución
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection