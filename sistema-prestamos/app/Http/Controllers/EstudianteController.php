<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Services\EstudianteService; // Importamos el servicio
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstudianteController extends Controller
{
    protected $estudianteService;

    // Inyección de Dependencia: Laravel nos da el servicio automáticamente
    public function __construct(EstudianteService $estudianteService)
    {
        $this->estudianteService = $estudianteService;
    }

    public function index(Request $request)
    {
        // Delegamos la búsqueda al servicio
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
        // Validamos aquí (esto es responsabilidad del controlador: validar entrada HTTP)
        $datos = $request->validate([
            'matricula' => 'required|unique:estudiantes,matricula',
            'nombre'    => 'required|string',
            'apellido'  => 'required|string',
            'email'     => 'required|email|unique:estudiantes,email',
            'carrera'   => 'required',
            'tipo' => 'required|in:estudiante,practicante',
            'telefono'  => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        // El servicio se encarga de guardar
        $this->estudianteService->crearEstudiante($datos);

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
            'tipo' => 'required|in:estudiante,practicante',
            'telefono'  => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        $this->estudianteService->actualizarEstudiante($estudiante, $datos);

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante actualizado correctamente.');
    }

    public function destroy(Estudiante $estudiante)
    {
        $this->estudianteService->eliminarEstudiante($estudiante);

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante eliminado correctamente.');
    }
}