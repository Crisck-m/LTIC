<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Equipo;
use App\Models\Prestamo;
use Carbon\Carbon;

/**
 * DashboardService
 *
 * Servicio que proporciona los datos estadísticos para el panel de control principal.
 * Centraliza los cálculos de métricas del sistema para mantener el controlador limpio.
 */
class DashboardService
{
    /**
     * Obtiene todas las estadísticas necesarias para el panel de control.
     *
     * Retorna un array asociativo con:
     * - `equipos_disponibles`: Cantidad de laptops con estado 'disponible'.
     * - `historial_prestamos`: Total de préstamos registrados hoy.
     * - `pendientes_devolucion`: Total de préstamos activos (aún no devueltos).
     * - `prestamos_atrasados`: Total de préstamos activos con fecha de devolución vencida.
     * - `ultimos_movimientos`: Los 5 préstamos más recientes con sus relaciones cargadas.
     *
     * @return array<string, mixed>
     */
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
     * Contar préstamos con fecha de devolución vencida.
     *
     * Un préstamo se considera atrasado si tiene estado 'activo' y su
     * `fecha_devolucion_esperada` es anterior al día de hoy.
     *
     * @return int Número de préstamos atrasados.
     */
    private function contarPrestamosAtrasados()
    {
        return Prestamo::where('estado', 'activo')
            ->where('fecha_devolucion_esperada', '<', Carbon::today())
            ->count();
    }
}