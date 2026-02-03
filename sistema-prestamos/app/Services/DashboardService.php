<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Equipo;
use App\Models\Prestamo;

class DashboardService
{
    public function obtenerEstadisticas()
    {
        return [
            'total_estudiantes' => Estudiante::count(),
            'equipos_disponibles' => Equipo::where('estado', 'disponible')->where('tipo', 'laptop')->count(),
            'historial_prestamos' => Prestamo::whereDate('created_at', today())->count(),
            'pendientes_devolucion' => Prestamo::where('estado', 'activo')->count(),
            'ultimos_movimientos' => Prestamo::with(['estudiante', 'equipo'])->latest()->take(5)->get()
        ];
    }
}