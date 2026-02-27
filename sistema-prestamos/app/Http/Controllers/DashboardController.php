<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

/**
 * DashboardController
 *
 * Controla la vista principal del sistema (panel de control).
 * Muestra estadísticas generales del estado actual del inventario y préstamos.
 */
class DashboardController extends Controller
{
    /**
     * Instancia del servicio de dashboard.
     *
     * @var DashboardService
     */
    protected $dashboardService;

    /**
     * Inyecta el servicio de dashboard al controlador.
     *
     * @param DashboardService $dashboardService Servicio que calcula las estadísticas del panel.
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Muestra la vista principal del dashboard con estadísticas del sistema.
     *
     * Obtiene datos como: equipos disponibles, préstamos del día,
     * devoluciones pendientes, préstamos atrasados y últimos movimientos.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $stats = $this->dashboardService->obtenerEstadisticas();

        // Pasamos la variable $stats a la vista
        return view('dashboard', compact('stats'));
    }
}