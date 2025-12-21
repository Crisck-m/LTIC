<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Estudiante;
use App\Models\Prestamo;
use App\Services\PrestamoService;
use Illuminate\Http\Request;

class PrestamoController extends Controller
{
    protected $prestamoService;

    public function __construct(PrestamoService $prestamoService)
    {
        $this->prestamoService = $prestamoService;
    }

    // Mostrar historial
    public function index(Request $request)
    {
        $prestamos = $this->prestamoService->listarPrestamos(
            $request->search,
            $request->estado
        );

        return view('prestamos.index', compact('prestamos'));
    }

    // Mostrar formulario de nuevo préstamo
    public function create()
    {
        // Esto es solo cargar datos para los select, no necesita servicio complejo
        $equipos = Equipo::where('estado', 'disponible')->get();
        $estudiantes = Estudiante::all();
        $pasantes = Estudiante::where('tipo', 'pasante')->get();

        return view('prestamos.create', compact('equipos', 'estudiantes', 'pasantes'));
    }

    // Guardar el nuevo préstamo
    public function store(Request $request)
    {
        $datos = $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'equipo_id'     => 'required|exists:equipos,id',
            'pasante_id'    => 'required|exists:estudiantes,id',
            'observaciones' => 'nullable|string'
        ]);

        $this->prestamoService->registrarSalida($datos);

        return redirect()->route('dashboard') // O a prestamos.index si prefieres
            ->with('success', 'Préstamo registrado correctamente.');
    }

    // Mostrar formulario de confirmación de devolución
    public function finalizar(Prestamo $prestamo)
    {
        return view('prestamos.finalizar', compact('prestamo'));
    }

    // Procesar la devolución
    public function devolver(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'observaciones' => 'nullable|string'
        ]);

        $this->prestamoService->registrarDevolucion(
            $prestamo, 
            $request->observaciones
        );

        return redirect()->route('prestamos.index')
            ->with('success', 'Equipo devuelto correctamente.');
    }
}