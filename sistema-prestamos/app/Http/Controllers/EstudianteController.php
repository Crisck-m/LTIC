<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Services\EstudianteService; 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Historial; 

class EstudianteController extends Controller
{
    protected $estudianteService;

    public function __construct(EstudianteService $estudianteService)
    {
        $this->estudianteService = $estudianteService;
    }

    public function index(Request $request)
    {
        $estudiantes = $this->estudianteService->listarEstudiantes(
            $request->search, 
            $request->tipo
        );

        return view('estudiantes.index', compact('estudiantes'));
    }

    public function create()
    {
        return view('estudiantes.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'matricula' => 'required|unique:estudiantes,matricula',
            'nombre'    => 'required|string',
            'apellido'  => 'required|string',
            'email'     => 'required|email|unique:estudiantes,email',
            'carrera'   => 'required',
            'tipo'      => 'required|in:estudiante,practicante',
            'telefono'  => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        $this->estudianteService->crearEstudiante($datos);

        Historial::registrar(
            'Nuevo Estudiante',
            "Se registró al estudiante {$request->nombre} {$request->apellido} con C.I. {$request->matricula}."
        );

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante registrado correctamente.');
    }

    public function edit(Estudiante $estudiante)
    {
        return view('estudiantes.edit', compact('estudiante'));
    }

    public function update(Request $request, Estudiante $estudiante)
    {
        $datos = $request->validate([
            'matricula' => ['required', Rule::unique('estudiantes')->ignore($estudiante->id)],
            'nombre'    => 'required|string',
            'apellido'  => 'required|string',
            'email'     => ['required', 'email', Rule::unique('estudiantes')->ignore($estudiante->id)],
            'carrera'   => 'required',
            'tipo'      => 'required|in:estudiante,practicante',
            'telefono'  => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        $this->estudianteService->actualizarEstudiante($estudiante, $datos);

        Historial::registrar(
            'Estudiante Actualizado',
            "Se modificaron los datos de {$estudiante->nombre} {$estudiante->apellido} (C.I. {$estudiante->matricula})."
        );

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante actualizado correctamente.');
    }

    public function destroy(Estudiante $estudiante)
    {
        $nombre = "{$estudiante->nombre} {$estudiante->apellido}";
        $cedula = $estudiante->matricula; 

        // Intentamos eliminar usando el servicio
        // El servicio verifica si tiene préstamos pendientes o históricos
        $eliminado = $this->estudianteService->eliminarEstudiante($estudiante);

        // --- MANEJO DE ERROR SI TIENE HISTORIAL ---
        if (!$eliminado) {
            return redirect()->route('estudiantes.index')
                ->with('error', 'No se puede eliminar: El estudiante tiene historial de préstamos asociados.');
        }

        // --- SI SE PUDO ELIMINAR, GUARDAMOS EL REGISTRO ---
        Historial::registrar(
            'Estudiante Eliminado',
            "Se eliminó del sistema al estudiante {$nombre} con C.I. {$cedula}."
        );

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante eliminado correctamente.');
    }
}