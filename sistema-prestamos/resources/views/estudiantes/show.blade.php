@extends('layouts.app')

@section('title', 'Detalle del Estudiante')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user text-primary"></i>
                        Detalle del Estudiante
                    </h5>
                    <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Matrícula:</th>
                                    <td>
                                        <strong>{{ $estudiante->matricula }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Nombre:</th>
                                    <td>{{ $estudiante->nombre }}</td>
                                </tr>
                                <tr>
                                    <th>Apellido:</th>
                                    <td>{{ $estudiante->apellido }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $estudiante->email }}</td>
                                </tr>
                                <tr>
                                    <th>Teléfono:</th>
                                    <td>{{ $estudiante->telefono ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Carrera:</th>
                                    <td>
                                        <span class="badge bg-info">{{ $estudiante->carrera }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tipo:</th>
                                    <td>
                                        @if($estudiante->tipo == 'pasante')
                                            <span class="badge bg-warning">Pasante</span>
                                        @else
                                            <span class="badge bg-primary">Estudiante</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        @if($estudiante->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Registrado:</th>
                                    <td>{{ $estudiante->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Actualizado:</th>
                                    <td>{{ $estudiante->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($estudiante->observaciones)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">Observaciones</h6>
                                </div>
                                <div class="card-body">
                                    {{ $estudiante->observaciones }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-warning me-2">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="{{ route('estudiantes.destroy', $estudiante) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('¿Estás seguro de eliminar este estudiante?')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection