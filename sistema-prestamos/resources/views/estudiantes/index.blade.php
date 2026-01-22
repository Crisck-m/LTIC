@extends('layouts.panel')

@section('titulo', 'Gestión de Estudiantes')

@section('contenido')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-secondary">
            <i class="fas fa-users me-2"></i>Listado de Alumnos
        </h5>
        <a href="{{ route('estudiantes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Estudiante
        </a>
    </div>

    <div class="card-body">
        
        <form action="{{ route('estudiantes.index') }}" method="GET">
            <div class="row mb-3">
                
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Buscar por nombre o cédula..." 
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="tipo" class="form-select">
                        <option value="">Todos los roles</option>
                        <option value="estudiante" {{ request('tipo') == 'estudiante' ? 'selected' : '' }}>Estudiante Regular</option>
                        <option value="practicante" {{ request('tipo') == 'practicante' ? 'selected' : '' }}>Practicante (Staff)</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>

                    @if(request('search') || request('tipo'))
                        <a href="{{ route('estudiantes.index') }}" class="btn btn-outline-danger">
                            <i class="fas fa-eraser"></i> Limpiar
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Carrera</th>
                        <th class="text-center">Rol</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($estudiantes as $estudiante)
                        <tr>
                            <td class="fw-bold">{{ $estudiante->cedula }}</td>
                            <td>
                                <span @if($estudiante->observaciones) data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $estudiante->observaciones }}" @endif>
                                    {{ $estudiante->nombre }} {{ $estudiante->apellido }}
                                </span>
                            </td>
                            <td>{{ $estudiante->email }}</td>
                            <td>{{ $estudiante->carrera }}</td>
                            <td class="text-center">
                                @if($estudiante->tipo == 'estudiante')
                                    <span class="badge bg-info text-dark">Estudiante</span>
                                @else
                                    <span class="badge bg-warning text-dark border border-dark">
                                        <i class="fas fa-id-badge me-1"></i> Practicante
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form action="{{ route('estudiantes.destroy', $estudiante) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas eliminar a este estudiante?')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="fas fa-user-slash fa-3x"></i></div>
                                <h6 class="text-muted">No se encontraron estudiantes con ese criterio.</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $estudiantes->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>
@endsection