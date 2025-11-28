@extends('layouts.app')

@section('title', 'Gestión de Estudiantes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users text-primary"></i>
                        Gestión de Estudiantes
                    </h5>
                    <a href="{{ route('estudiantes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Estudiante
                    </a>
                </div>
                <div class="card-body">
                    <!-- Barra de búsqueda -->
                    <form action="{{ route('estudiantes.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Buscar por matrícula, nombre, apellido o email..."
                                           value="{{ request('search') }}">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ route('estudiantes.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Tabla de estudiantes -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th>Carrera</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estudiantes as $estudiante)
                                <tr>
                                    <td>
                                        <strong>{{ $estudiante->matricula }}</strong>
                                    </td>
                                    <td>{{ $estudiante->nombre_completo }}</td>
                                    <td>{{ $estudiante->email }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $estudiante->carrera }}</span>
                                    </td>
                                    <td>
                                        @if($estudiante->tipo == 'pasante')
                                            <span class="badge bg-warning">Pasante</span>
                                        @else
                                            <span class="badge bg-primary">Estudiante</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($estudiante->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('estudiantes.show', $estudiante) }}" 
                                               class="btn btn-info btn-sm" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('estudiantes.edit', $estudiante) }}" 
                                               class="btn btn-warning btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('estudiantes.destroy', $estudiante) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        title="Eliminar"
                                                        onclick="return confirm('¿Estás seguro de eliminar este estudiante?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <br>
                                        No se encontraron estudiantes registrados.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $estudiantes->firstItem() }} - {{ $estudiantes->lastItem() }} 
                            de {{ $estudiantes->total() }} estudiantes
                        </div>
                        {{ $estudiantes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection