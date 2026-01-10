@extends('layouts.panel')

@section('titulo', 'Historial de Operaciones')

@section('contenido')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-secondary"><i class="fas fa-history me-2"></i>Bitácora del Sistema</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Usuario (Staff)</th>
                        <th>Acción</th>
                        <th>Detalles</th>
                        <th>Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historiales as $evento)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">
                            <i class="fas fa-user-shield me-1"></i> {{ $evento->usuario->name ?? 'Sistema' }}
                        </td>
                        <td>
                            @if(str_contains($evento->accion, 'Préstamo'))
                                <span class="badge bg-warning text-dark">{{ $evento->accion }}</span>
                            @elseif(str_contains($evento->accion, 'Devolución'))
                                <span class="badge bg-success">{{ $evento->accion }}</span>
                            @elseif(str_contains($evento->accion, 'Estudiante'))
                                <span class="badge bg-info text-dark">{{ $evento->accion }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $evento->accion }}</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $evento->detalles }}</td>
                        <td>
                            <div>{{ $evento->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $evento->created_at->format('H:i A') }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $historiales->links() }}
        </div>
    </div>
</div>
@endsection