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
            'cedula' => 'required|unique:estudiantes,cedula|digits:10',  // ✅ CAMBIADO
            'nombre'    => 'required|string',
            'apellido'  => 'required|string',
            'email'     => 'required|email|unique:estudiantes,email',
            'carrera'   => 'required',
            'tipo'      => 'required|in:estudiante,practicante',
            'telefono'  => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        // Si carrera es 'Otra', usar el valor de otra_carrera
        if ($request->carrera === 'Otra') {
            $datos['carrera'] = $request->validate([
                'otra_carrera' => 'required|string'
            ])['otra_carrera'];
        }

        $estudiante = Estudiante::create($datos);

        Historial::registrar(
            'Nuevo Estudiante',
            "Se registró al estudiante {$request->nombre} {$request->apellido} con C.I. {$request->cedula}."  // ✅ CAMBIADO
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
            'cedula' => ['required', 'digits:10', Rule::unique('estudiantes')->ignore($estudiante->id)],  // ✅ CAMBIADO
            'nombre'    => 'required|string',
            'apellido'  => 'required|string',
            'email'     => ['required', 'email', Rule::unique('estudiantes')->ignore($estudiante->id)],
            'carrera'   => 'required',
            'tipo'      => 'required|in:estudiante,practicante',
            'telefono'  => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        // Si carrera es 'Otra', usar el valor de otra_carrera
        if ($request->carrera === 'Otra') {
            $datos['carrera'] = $request->validate([
                'otra_carrera' => 'required|string'
            ])['otra_carrera'];
        }

        $this->estudianteService->actualizarEstudiante($estudiante, $datos);

        Historial::registrar(
            'Estudiante Actualizado',
            "Se modificaron los datos de {$estudiante->nombre} {$estudiante->apellido} (C.I. {$estudiante->cedula})."  // ✅ CAMBIADO
        );

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante actualizado correctamente.');
    }

    public function destroy(Estudiante $estudiante)
    {
        $nombre = "{$estudiante->nombre} {$estudiante->apellido}";
        $cedula = $estudiante->cedula;  // ✅ CAMBIADO

        // Intentamos eliminar usando el servicio
        $eliminado = $this->estudianteService->eliminarEstudiante($estudiante);

        if (!$eliminado) {
            return redirect()->route('estudiantes.index')
                ->with('error', 'No se puede eliminar: El estudiante tiene historial de préstamos asociados.');
        }

        Historial::registrar(
            'Estudiante Eliminado',
            "Se eliminó del sistema al estudiante {$nombre} con C.I. {$cedula}."
        );

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante eliminado correctamente.');
    }

    /**
     * 🔍 Búsqueda AJAX para autocompletado (NUEVO)
     */
    public function buscarAjax(Request $request)
    {
        $search = $request->get('q');
        
        $estudiantes = Estudiante::where('activo', true)
            ->where(function($query) use ($search) {
                $query->where('cedula', 'LIKE', "%{$search}%")  // ✅ CAMBIADO
                      ->orWhere('nombre', 'LIKE', "%{$search}%")
                      ->orWhere('apellido', 'LIKE', "%{$search}%")
                      ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$search}%"]);
            })
            ->limit(10)
            ->get(['id', 'cedula', 'nombre', 'apellido', 'carrera']);  // ✅ CAMBIADO
        
        return response()->json($estudiantes);
    }
}