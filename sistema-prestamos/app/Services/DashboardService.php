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
            'equipos_disponibles' => Equipo::where('estado', 'disponible')->count(),
            'prestamos_activos' => Prestamo::where('estado', 'activo')->count(),
            'pendientes_devolucion' => Prestamo::where('estado', 'activo')->count(), // O tu lógica específica
            'ultimos_movimientos' => Prestamo::with(['estudiante', 'equipo'])->latest()->take(5)->get()
        ];
    }
}