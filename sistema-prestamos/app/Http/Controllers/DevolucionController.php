<?php

namespace App\Http\Controllers;

use App\Services\PrestamoService;
use Illuminate\Http\Request;

/**
 * DevolucionController
 *
 * Gestiona la vista de devoluciones pendientes.
 * Lista todos los préstamos activos que aún no han sido devueltos.
 */
class DevolucionController extends Controller
{
    /**
     * Instancia del servicio de préstamos.
     *
     * @var PrestamoService
     */
    protected $prestamoService;

    /**
     * Inyecta el servicio de préstamos al controlador.
     *
     * @param PrestamoService $prestamoService Servicio que maneja la lógica de préstamos y devoluciones.
     */
    public function __construct(PrestamoService $prestamoService)
    {
        $this->prestamoService = $prestamoService;
    }

    /**
     * Muestra la lista de préstamos pendientes de devolución.
     *
     * Reutiliza la lógica del PrestamoService para obtener los préstamos
     * con estado 'activo', ordenados del más reciente al más antiguo.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Reutilizamos la lógica del servicio
        $pendientes = $this->prestamoService->obtenerPendientes();

        return view('devoluciones.index', compact('pendientes'));
    }
}