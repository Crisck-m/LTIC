@extends('layouts.panel')

@section('titulo', 'Gestión de Estudiantes')

@section('contenido')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Alumnos</h5>
        <a href="{{ route('estudiantes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Estudiante
        </a>
    </div>
    <div class="card-body">
        
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Buscar por nombre o matrícula...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Carrera</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($estudiantes as $estudiante)
                        <tr>
                            <td class="fw-bold">{{ $estudiante->matricula }}</td>
                            <td>
                                {{ $estudiante->nombre }} {{ $estudiante->apellido }}
                            </td>
                            <td>{{ $estudiante->email }}</td>
                            <td>{{ $estudiante->carrera }}</td>
                            <td>
                                @if($estudiante->tipo == 'estudiante')
                                    <span class="badge bg-info text-dark">Estudiante</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pasante</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('estudiantes.destroy', $estudiante) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar?')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">No hay estudiantes registrados aún.</div>
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
@endsection