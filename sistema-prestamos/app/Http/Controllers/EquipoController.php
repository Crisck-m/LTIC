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
        // VALIDACIÓN SEPARADA POR TIPO
        if ($request->tipo === 'Laptop') {
            // LAPTOP: Validar nombre único
            $datos = $request->validate([
                'nombre_equipo' => 'required|string|max:100|unique:equipos,nombre_equipo',
                'tipo' => 'required|string|max:50',
                'marca' => 'required|string|max:50',
                'modelo' => 'required|string|max:50',
                'caracteristicas' => 'nullable|string',
                'estado' => 'required|in:disponible,prestado,mantenimiento,dado_de_baja',
            ], [
                'nombre_equipo.unique' => 'Ya existe un equipo con ese nombre/código.',
            ]);

            $datos['es_individual'] = true;
            $datos['cantidad_total'] = 1;
            $datos['cantidad_disponible'] = ($datos['estado'] == 'disponible') ? 1 : 0;

        } else {
            // EQUIPOS POR CANTIDAD: NO validar nombre único
            $datos = $request->validate([
                'tipo' => 'required|string|max:50',
                'tipo_otro' => 'required_if:tipo,Otro|nullable|string|max:100',
                'cantidad_total' => 'required|integer|min:1',
                'marca' => 'required|string|max:50',
                'modelo' => 'required|string|max:50',
                'caracteristicas' => 'nullable|string',
                'estado' => 'required|in:disponible,prestado,mantenimiento,dado_de_baja',
            ]);

            // Determinar el tipo real
            $tipoReal = ($request->tipo === 'Otro' && !empty($request->tipo_otro))
                ? trim($request->tipo_otro)
                : $request->tipo;

            $datos['nombre_equipo'] = $tipoReal;
            $datos['es_individual'] = false;

            // Si es "Otro", guardar tipo personalizado
            if ($request->tipo === 'Otro' && !empty($request->tipo_otro)) {
                $datos['tipo_personalizado'] = trim($request->tipo_otro);
            }

            // ===================================================================
            // VERIFICAR SI YA EXISTE UN EQUIPO DEL MISMO TIPO/MARCA/MODELO
            // Si existe, INCREMENTAR cantidad en lugar de crear nuevo registro
            // ===================================================================
            $equipoExistente = Equipo::where('nombre_equipo', $tipoReal)
                ->where('marca', $datos['marca'])
                ->where('modelo', $datos['modelo'])
                ->where('es_individual', false)
                ->first();

            if ($equipoExistente) {
                // INCREMENTAR cantidad del equipo existente
                $equipoExistente->cantidad_total += $request->cantidad_total;

                if ($datos['estado'] == 'disponible') {
                    $equipoExistente->cantidad_disponible += $request->cantidad_total;
                }

                $equipoExistente->save();

                return redirect()->route('equipos.index')
                    ->with('success', "✅ Se agregaron {$request->cantidad_total} unidad(es) al inventario existente.\n\n" .
                        "• Total: {$equipoExistente->cantidad_total} unidades\n" .
                        "• Disponibles: {$equipoExistente->cantidad_disponible} unidades");
            }

            // Si NO existe, configurar cantidades para nuevo registro
            $datos['cantidad_disponible'] = ($datos['estado'] == 'disponible') ? $request->cantidad_total : 0;
        }

        // Remover campo temporal
        unset($datos['tipo_otro']);
        $datos['user_id'] = auth()->id();

        try {
            Equipo::create($datos);

            if ($request->tipo === 'Laptop') {
                return redirect()->route('equipos.index')
                    ->with('success', 'Laptop registrada correctamente.');
            } else {
                return redirect()->route('equipos.index')
                    ->with('success', "Equipo registrado correctamente.\n\n" .
                        "• Tipo: {$datos['nombre_equipo']}\n" .
                        "• Cantidad: {$datos['cantidad_total']} unidades");
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Error al registrar: ' . $e->getMessage()])
                ->withInput();
        }
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