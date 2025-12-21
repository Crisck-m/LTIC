<?php

namespace App\Http\Controllers;

use App\Services\PrestamoService;
use Illuminate\Http\Request;

class DevolucionController extends Controller
{
    protected $prestamoService;

    public function __construct(PrestamoService $prestamoService)
    {
        $this->prestamoService = $prestamoService;
    }

    public function index()
    {
        // Reutilizamos la lógica del servicio
        $pendientes = $this->prestamoService->obtenerPendientes();

        return view('devoluciones.index', compact('pendientes'));
    }
}