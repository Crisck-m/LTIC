<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Services\EstudianteService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $carreras = \App\Models\Carrera::orderBy('nombre')->get();
        return view('estudiantes.create', compact('carreras'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cedula' => 'required|string|max:20|unique:estudiantes,cedula',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'telefono' => 'nullable|string|max:15',
            'carrera_id' => 'required_without:carrera_otra',
            'carrera_otra' => 'required_if:carrera_id,otra|nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500',
            'tipo' => 'required|in:estudiante,practicante'
        ], [
            'carrera_id.required_without' => 'Debes seleccionar una carrera o especificar una nueva.',
            'carrera_otra.required_if' => 'Debes especificar el nombre de la carrera.',
        ]);

        // ===================================================================
        // LÓGICA: Si seleccionó "Otra", crear la carrera automáticamente
        // ===================================================================
        if ($request->carrera_id === 'otra' && !empty($request->carrera_otra)) {
            // Verificar si la carrera ya existe (por si alguien más la creó)
            $carrera = \App\Models\Carrera::firstOrCreate(
                ['nombre' => trim($request->carrera_otra)]
            );

            $datos['carrera_id'] = $carrera->id;
            $datos['carrera'] = $carrera->nombre; // Por compatibilidad
        } else {
            // Usar la carrera seleccionada
            $carrera = \App\Models\Carrera::find($request->carrera_id);
            $datos['carrera'] = $carrera ? $carrera->nombre : null;
        }

        // Remover campo temporal
        unset($datos['carrera_otra']);

        try {
            Estudiante::create($datos);

            return redirect()->route('estudiantes.index')
                ->with('success', 'Estudiante registrado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Error al registrar el estudiante: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(Estudiante $estudiante)
    {
        return view('estudiantes.edit', compact('estudiante'));
    }

    public function update(Request $request, Estudiante $estudiante)
    {
        $datos = $request->validate([
            'cedula' => ['required', 'digits:10', Rule::unique('estudiantes')->ignore($estudiante->id)],  // ✅ CAMBIADO
            'nombre' => 'required|string',
            'apellido' => 'required|string',
            'email' => ['required', 'email', Rule::unique('estudiantes')->ignore($estudiante->id)],
            'carrera_id' => 'required',
            'tipo' => 'required|in:estudiante,practicante',
            'telefono' => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        // Si carrera es 'Otra', usar el valor de otra_carrera
        if ($request->carrera_id === 'Otra') {
            $datos['carrera_id'] = $request->validate([
                'otra_carrera' => 'required|string'
            ])['otra_carrera'];
        }

        $this->estudianteService->actualizarEstudiante($estudiante, $datos);

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
                ->with('error', 'No se puede eliminar: El estudiante tiene préstamos asociados.');
        }

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
            ->where(function ($query) use ($search) {
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