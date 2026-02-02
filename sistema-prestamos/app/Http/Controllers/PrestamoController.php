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
        // Validar que fecha_hasta no sea anterior a fecha_desde
        $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ], [
            'fecha_hasta.after_or_equal' => 'La fecha "Hasta" no puede ser anterior a la fecha "Desde".',
        ]);

        $prestamos = $this->prestamoService->listarPrestamos(
            $request->search,
            $request->estado,
            $request->fecha_desde,
            $request->fecha_hasta
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
            'estudiante_id' => 'required|exists:estudiantes,id',
            'equipo_id' => 'required|exists:equipos,id',
            'practicante_id' => 'required|exists:estudiantes,id',
            'observaciones' => 'nullable|string',
            'fecha_devolucion_esperada' => 'required|date|after_or_equal:today',
            'notificar_retorno' => 'nullable|boolean',
            'periodo_notificacion' => 'nullable|in:1_dia,1_semana,1_mes'
        ], [
            'fecha_devolucion_esperada.after_or_equal' => 'La fecha de devolución no puede ser anterior al día de hoy.',
        ]);

        // Validación previa: verificar que el equipo está disponible
        $equipo = Equipo::find($datos['equipo_id']);
        if ($equipo->estado !== 'disponible') {
            return redirect()
                ->back()
                ->withErrors([
                    'equipo_id' => 'Este equipo fue prestado recientemente por otro usuario. Por favor, selecciona otro equipo disponible.'
                ])
                ->withInput();
        }

        $datos['notificar_retorno'] = $request->boolean('notificar_retorno');
        if ($datos['notificar_retorno'] && empty($datos['periodo_notificacion'])) {
            $datos['periodo_notificacion'] = '1_dia';
        }

        try {
            $this->prestamoService->registrarSalida($datos);

            return redirect()->route('dashboard')
                ->with('success', 'Préstamo registrado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function finalizar(Prestamo $prestamo)
    {
        $practicantes = Estudiante::where('tipo', 'practicante')->orderBy('nombre')->get();
        return view('prestamos.finalizar', compact('prestamo', 'practicantes'));
    }

    public function devolver(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'practicante_devolucion_id' => 'required|exists:estudiantes,id',
            'observaciones_devolucion' => 'nullable|string'
        ]);

        // Validación previa: verificar que el préstamo está activo
        if ($prestamo->estado !== 'activo') {
            return redirect()
                ->back()
                ->withErrors([
                    'error' => 'Este préstamo ya fue procesado por otro usuario.'
                ]);
        }

        try {
            $this->prestamoService->registrarDevolucion(
                $prestamo,
                $request->observaciones_devolucion,
                $request->practicante_devolucion_id
            );

            return redirect()->route('prestamos.index')
                ->with('success', 'Equipo devuelto correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}