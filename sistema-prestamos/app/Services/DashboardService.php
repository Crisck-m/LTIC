<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Equipo;
use App\Models\Prestamo;
use Carbon\Carbon;

class DashboardService
{
    public function obtenerEstadisticas()
    {
        return [
            'equipos_disponibles' => Equipo::where('estado', 'disponible')->where('tipo', 'laptop')->count(),
            'historial_prestamos' => Prestamo::whereDate('created_at', today())->count(),
            'pendientes_devolucion' => Prestamo::where('estado', 'activo')->count(),
            'prestamos_atrasados' => $this->contarPrestamosAtrasados(),
            'ultimos_movimientos' => Prestamo::with(['estudiante', 'prestamoEquipos.equipo'])
                ->latest()
                ->take(5)
                ->get()
        ];
    }

    /**
     * Contar préstamos con fecha de devolución vencida
     */
    private function contarPrestamosAtrasados()
    {
        return Prestamo::where('estado', 'activo')
            ->where('fecha_devolucion_esperada', '<', Carbon::today())
            ->count();
    }
}