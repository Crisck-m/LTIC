<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Services\EquipoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class EquipoController extends Controller
{
    protected $equipoService;

    public function __construct(EquipoService $equipoService)
    {
        $this->equipoService = $equipoService;
    }

    public function index(Request $request)
    {
        $equipos = $this->equipoService->listarEquipos(
            $request->search,
            $request->tipo,
            $request->estado
        );
        return view('equipos.index', compact('equipos'));
    }

    public function create()
    {
        return view('equipos.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'tipo' => 'required|string',
            'marca' => 'required|string',
            'modelo' => 'required|string',
            'nombre_equipo' => 'required|unique:equipos,nombre_equipo',
            'estado' => 'required|in:disponible,prestado,mantenimiento,baja',
            'caracteristicas' => 'nullable|string'
        ]);

        $equipo = $this->equipoService->crearEquipo($datos);

        return redirect()->route('equipos.index')->with('success', 'Equipo registrado correctamente.');
    }

    public function edit(Equipo $equipo)
    {
        return view('equipos.edit', compact('equipo'));
    }

    public function update(Request $request, Equipo $equipo)
    {
        $datos = $request->validate([
            'tipo' => 'required|string',
            'marca' => 'required|string',
            'modelo' => 'required|string',
            'nombre_equipo' => ['required', Rule::unique('equipos')->ignore($equipo->id)],
            'estado' => 'required|in:disponible,prestado,mantenimiento,baja',
            'caracteristicas' => 'nullable|string'
        ]);

        $this->equipoService->actualizarEquipo($equipo, $datos);

        return redirect()->route('equipos.index')->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy(Equipo $equipo)
    {
        $codigo = $equipo->nombre_equipo;
        $desc = "{$equipo->tipo} {$equipo->marca}";

        $eliminado = $this->equipoService->eliminarEquipo($equipo);

        if (!$eliminado) {
            return redirect()->route('equipos.index')
                ->with('error', 'No se puede eliminar: El equipo tiene préstamos asociados.');
        }

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo eliminado correctamente.');
    }

    /**
     * Búsqueda AJAX para autocompletado de equipos
     */
    public function buscarAjax(Request $request)
    {
        try {
            $search = $request->get('q', '');

            if (empty($search)) {
                return response()->json([]);
            }

            $equipos = Equipo::where('estado', 'disponible')
                ->where(function ($query) use ($search) {
                    $query->where('nombre_equipo', 'LIKE', "%{$search}%")
                        ->orWhere('tipo', 'LIKE', "%{$search}%")
                        ->orWhere('marca', 'LIKE', "%{$search}%")
                        ->orWhere('modelo', 'LIKE', "%{$search}%");
                })
                ->limit(10)
                ->get(['id', 'tipo', 'marca', 'modelo', 'nombre_equipo']);

            return response()->json($equipos);

        } catch (\Exception $e) {
            Log::error('Error en búsqueda de equipos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error en la búsqueda',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estados de todos los equipos para polling
     * Retorna solo id y estado para optimizar rendimiento
     */
    public function obtenerEstados()
    {
        try {
            $equipos = Equipo::select('id', 'estado')
                ->get()
                ->map(function ($equipo) {
                    return [
                        'id' => $equipo->id,
                        'estado' => $equipo->estado
                    ];
                });

            return response()->json($equipos);
        } catch (\Exception $e) {
            Log::error('Error obteniendo estados de equipos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error obteniendo estados'
            ], 500);
        }
    }
}