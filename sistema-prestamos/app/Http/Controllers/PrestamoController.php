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

    public function index(Request $request)
    {
        $prestamos = $this->prestamoService->listarPrestamos(
            $request->search,
            $request->estado
        );

        return view('prestamos.index', compact('prestamos'));
    }

    public function create()
    {
        $equipos = Equipo::where('estado', 'disponible')->get();
        $estudiantes = Estudiante::all();
        
        $practicantes = Estudiante::where('tipo', 'practicante')->get(); 

        return view('prestamos.create', compact('equipos', 'estudiantes', 'practicantes'));
    }

    public function store(Request $request)
    {
        // Validación directa
        $datos = $request->validate([
            'estudiante_id'  => 'required|exists:estudiantes,id',
            'equipo_id'      => 'required|exists:equipos,id',
            'practicante_id' => 'required|exists:estudiantes,id',
            'observaciones'  => 'nullable|string',
            'fecha_devolucion_esperada' => 'required|date|after:now',
            'notificar_retorno' => 'boolean',
            'periodo_notificacion' => 'nullable|in:1_dia,1_semana,1_mes'
        ]);

        $this->prestamoService->registrarSalida($datos);

        return redirect()->route('dashboard')
            ->with('success', 'Préstamo registrado correctamente.');
    }

    public function finalizar(Prestamo $prestamo)
    {
        return view('prestamos.finalizar', compact('prestamo'));
    }

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