<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Services\EquipoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

/**
 * EquipoController
 *
 * Controlador CRUD para la gestión del inventario de equipos.
 * Maneja dos tipos de equipos:
 * - **Individuales** (ej: Laptops): identificadas por nombre/código único, con marca y modelo.
 * - **Por cantidad** (ej: auriculares, cables): agrupados por tipo, con stock total y disponible.
 *
 * También expone endpoints AJAX para autocompletado y polling de estados.
 */
class EquipoController extends Controller
{
    /**
     * Instancia del servicio de equipos.
     *
     * @var EquipoService
     */
    protected $equipoService;

    /**
     * Inyecta el servicio de equipos al controlador.
     *
     * @param EquipoService $equipoService Servicio que encapsula la lógica de negocio de equipos.
     */
    public function __construct(EquipoService $equipoService)
    {
        $this->equipoService = $equipoService;
    }

    /**
     * Muestra el listado de equipos con filtros de búsqueda.
     *
     * Acepta los parámetros de query:
     * - `search`: filtra por marca, modelo, nombre o tipo.
     * - `tipo`: filtra por tipo de equipo.
     * - `estado`: filtra por estado (disponible, prestado, mantenimiento, etc.).
     *
     * @param  Request $request Petición HTTP con los filtros opcionales.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $equipos = $this->equipoService->listarEquipos(
            $request->search,
            $request->tipo,
            $request->estado
        );
        return view('equipos.index', compact('equipos'));
    }

    /**
     * Muestra el formulario para registrar un nuevo equipo.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('equipos.create');
    }

    /**
     * Almacena un nuevo equipo en el inventario.
     *
     * La lógica de validación y creación varía según el tipo de equipo:
     *
     * **Laptop (equipo individual)**:
     * - El nombre/código del equipo debe ser único.
     * - Se validan marca, modelo y estado completo.
     * - Se establece `es_individual = true` y `cantidad_total = 1`.
     *
     * **Equipo por cantidad** (otros tipos):
     * - Solo se valida tipo y cantidad.
     * - Si ya existe un equipo del mismo tipo, se INCREMENTA su cantidad en lugar de crear un nuevo registro.
     * - Si el tipo es "Otro", se permite especificar un nombre personalizado.
     * - Marca y modelo se almacenan como 'N/A'.
     *
     * @param  Request $request Petición HTTP con los datos del formulario.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // VALIDACIÓN SEPARADA POR TIPO
        if ($request->tipo === 'Laptop') {
            // LAPTOP: Validar todos los campos incluyendo marca/modelo
            $datos = $request->validate([
                'nombre_equipo' => 'required|string|max:100|unique:equipos,nombre_equipo',
                'tipo' => 'required|string|max:50',
                'marca' => 'required|string|max:50',
                'modelo' => 'required|string|max:50',
                'caracteristicas' => 'nullable|string',
                'estado' => 'required|in:disponible,prestado,mantenimiento,dado_de_baja',
            ], [
                'nombre_equipo.unique' => 'Ya existe un equipo con ese nombre/código.',
                'nombre_equipo.required' => 'El nombre/código es obligatorio para laptops.',
            ]);

            $datos['es_individual'] = true;
            $datos['cantidad_total'] = 1;
            $datos['cantidad_disponible'] = ($datos['estado'] == 'disponible') ? 1 : 0;

        } else {
            // EQUIPOS POR CANTIDAD: Solo validar tipo y cantidad
            // Marca y modelo NO son necesarios
            $datos = $request->validate([
                'tipo' => 'required|string|max:50',
                'tipo_otro' => 'required_if:tipo,Otro|nullable|string|max:100',
                'cantidad_total' => 'required|integer|min:1',
                'estado_simple' => 'nullable|in:disponible,mantenimiento',
            ], [
                'cantidad_total.required' => 'La cantidad es obligatoria.',
                'cantidad_total.min' => 'La cantidad debe ser al menos 1.',
            ]);

            // Determinar el tipo real
            $tipoReal = ($request->tipo === 'Otro' && !empty($request->tipo_otro))
                ? trim($request->tipo_otro)
                : $request->tipo;

            $datos['nombre_equipo'] = $tipoReal;
            $datos['es_individual'] = false;

            // Valores por defecto para equipos por cantidad
            $datos['marca'] = 'N/A';
            $datos['modelo'] = 'N/A';
            $datos['caracteristicas'] = null;
            $datos['estado'] = $request->estado_simple ?? 'disponible';

            // Si es "Otro", guardar tipo personalizado
            if ($request->tipo === 'Otro' && !empty($request->tipo_otro)) {
                $datos['tipo_personalizado'] = trim($request->tipo_otro);
            }

            // ===================================================================
            // VERIFICAR SI YA EXISTE UN EQUIPO DEL MISMO TIPO
            // Si existe, INCREMENTAR cantidad en lugar de crear nuevo registro
            // ===================================================================
            $equipoExistente = Equipo::where('nombre_equipo', $tipoReal)
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
                        "• Tipo: {$tipoReal}\n" .
                        "• Total: {$equipoExistente->cantidad_total} unidades\n" .
                        "• Disponibles: {$equipoExistente->cantidad_disponible} unidades");
            }

            // Si NO existe, configurar cantidades para nuevo registro
            $datos['cantidad_disponible'] = ($datos['estado'] == 'disponible') ? $request->cantidad_total : 0;
        }

        // Remover campo temporal
        if (isset($datos['tipo_otro'])) {
            unset($datos['tipo_otro']);
        }
        if (isset($datos['estado_simple'])) {
            unset($datos['estado_simple']);
        }

        $datos['user_id'] = auth()->id();

        try {
            $equipoCreado = Equipo::create($datos);

            if ($request->tipo === 'Laptop') {
                return redirect()->route('equipos.index')
                    ->with('success', '✅ Laptop registrada correctamente.');
            } else {
                return redirect()->route('equipos.index')
                    ->with('success', "✅ Equipo registrado correctamente.\n\n" .
                        "• Tipo: {$datos['nombre_equipo']}\n" .
                        "• Cantidad: {$datos['cantidad_total']} unidades\n" .
                        "• Disponibles: {$datos['cantidad_disponible']} unidades");
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear equipo: ' . $e->getMessage());
            \Log::error('Datos intentados: ' . json_encode($datos));

            return redirect()->back()
                ->withErrors(['error' => 'Error al registrar el equipo: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Muestra el formulario de edición de un equipo existente.
     *
     * @param  Equipo $equipo El equipo a editar (resuelto por Route Model Binding).
     * @return \Illuminate\View\View
     */
    public function edit(Equipo $equipo)
    {
        return view('equipos.edit', compact('equipo'));
    }

    /**
     * Actualiza los datos de un equipo existente en el inventario.
     *
     * La lógica de actualización varía según el tipo de equipo:
     *
     * **Laptop (equipo individual)**:
     * - Permite editar tipo, marca, modelo, nombre/código, estado y características.
     * - Delega la actualización al servicio.
     *
     * **Equipo por cantidad**:
     * - Solo permite actualizar la cantidad total y el estado.
     * - La nueva cantidad total no puede ser menor a las unidades actualmente prestadas.
     * - Si el estado cambia a 'mantenimiento', la cantidad disponible se pone en 0.
     * - La cantidad disponible se recalcula como: cantidad_total - unidades_prestadas.
     *
     * @param  Request $request Petición HTTP con los datos actualizados.
     * @param  Equipo  $equipo  El equipo a actualizar (resuelto por Route Model Binding).
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Equipo $equipo)
    {
        if ($equipo->es_individual) {
            // ===================================================================
            // LAPTOP (EQUIPO INDIVIDUAL): Actualizar todos los campos
            // ===================================================================
            $datos = $request->validate([
                'tipo' => 'required|string',
                'marca' => 'required|string',
                'modelo' => 'required|string',
                'nombre_equipo' => ['required', Rule::unique('equipos')->ignore($equipo->id)],
                'estado' => 'required|in:disponible,prestado,mantenimiento,baja',
                'caracteristicas' => 'nullable|string'
            ]);

            $this->equipoService->actualizarEquipo($equipo, $datos);

            return redirect()->route('equipos.index')
                ->with('success', '✅ Laptop actualizada correctamente.');

        } else {
            // ===================================================================
            // EQUIPO POR CANTIDAD: Solo actualizar cantidad y estado
            // ===================================================================

            // Calcular cuántas unidades están prestadas actualmente
            $cantidadPrestada = $equipo->cantidad_total - $equipo->cantidad_disponible;

            $datos = $request->validate([
                'cantidad_total' => [
                    'required',
                    'integer',
                    'min:' . $cantidadPrestada
                ],
                'estado' => 'required|in:disponible,mantenimiento',
            ], [
                'cantidad_total.min' => "La cantidad no puede ser menor a {$cantidadPrestada} porque hay unidades actualmente prestadas.",
            ]);

            // Calcular nueva cantidad disponible
            // Mantener la misma cantidad prestada, ajustar solo la disponible
            $nuevaCantidadDisponible = $datos['cantidad_total'] - $cantidadPrestada;

            // Si el estado cambia a mantenimiento, poner disponible en 0
            if ($datos['estado'] == 'mantenimiento') {
                $nuevaCantidadDisponible = 0;
            }

            $equipo->cantidad_total = $datos['cantidad_total'];
            $equipo->cantidad_disponible = $nuevaCantidadDisponible;
            $equipo->estado = $datos['estado'];
            $equipo->save();

            return redirect()->route('equipos.index')
                ->with('success', "✅ Cantidad actualizada correctamente.\n\n" .
                    "• Tipo: {$equipo->nombre_equipo}\n" .
                    "• Cantidad Total: {$equipo->cantidad_total}\n" .
                    "• Disponibles: {$equipo->cantidad_disponible}\n" .
                    "• Prestadas: {$cantidadPrestada}");
        }
    }

    /**
     * Elimina o da de baja un equipo del inventario.
     *
     * El resultado depende de si el equipo tiene préstamos registrados:
     * - **Con préstamos**: se marca como 'dado_de_baja' (no se elimina físicamente).
     * - **Sin préstamos**: se elimina permanentemente de la base de datos.
     *
     * El controlador delega la decisión al `EquipoService::eliminarEquipo()`.
     *
     * @param  Equipo $equipo El equipo a procesar (resuelto por Route Model Binding).
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Equipo $equipo)
    {
        $codigo = $equipo->nombre_equipo;
        $desc = "{$equipo->tipo} {$equipo->marca}";

        $resultado = $this->equipoService->eliminarEquipo($equipo);

        if ($resultado === 'dado_de_baja') {
            return redirect()->route('equipos.index')
                ->with('success', "✅ Equipo dado de baja correctamente.\n\n" .
                    "{$codigo} - {$desc}\n\n" .
                    "ℹ️ El equipo tenía préstamos registrados, por lo que se marcó como 'Dado de Baja' en lugar de eliminarse del sistema.");
        } elseif ($resultado === 'eliminado') {
            return redirect()->route('equipos.index')
                ->with('success', "✅ Equipo eliminado correctamente.\n\n" .
                    "{$codigo} - {$desc}\n\n" .
                    "El equipo fue eliminado permanentemente del sistema.");
        } else {
            return redirect()->route('equipos.index')
                ->with('error', '❌ Error al procesar la solicitud. Intenta de nuevo.');
        }
    }

    /**
     * Búsqueda AJAX para autocompletado de equipos en el formulario de préstamos.
     *
     * Retorna hasta 10 equipos **disponibles** que coincidan con el término de búsqueda.
     * Busca en: nombre_equipo, tipo, marca y modelo.
     * Solo incluye equipos con `cantidad_disponible > 0`.
     *
     * El resultado formatea cada equipo diferente según su tipo:
     * - **Laptop**: muestra nombre único + marca/modelo.
     * - **Otros**: muestra nombre + cantidad disponible/total.
     *
     * @param  Request $request Petición HTTP con el parámetro `q` (término de búsqueda).
     * @return \Illuminate\Http\JsonResponse Lista de equipos disponibles con datos de visualización.
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
                ->where(function ($query) {
                    // Solo mostrar equipos que tengan stock disponible
                    $query->where(function ($subQuery) {
                        // Equipos individuales (laptops)
                        $subQuery->where('es_individual', true)
                            ->where('cantidad_disponible', '>', 0);
                    })
                        ->orWhere(function ($subQuery) {
                        // Equipos por cantidad
                        $subQuery->where('es_individual', false)
                            ->where('cantidad_disponible', '>', 0);
                    });
                })
                ->limit(10)
                ->get()
                ->map(function ($equipo) {
                    if ($equipo->es_individual) {
                        // LAPTOP: Mostrar nombre único
                        return [
                            'id' => $equipo->id,
                            'tipo' => $equipo->tipo,
                            'marca' => $equipo->marca,
                            'modelo' => $equipo->modelo,
                            'nombre_equipo' => $equipo->nombre_equipo,
                            'es_individual' => true,
                            'display' => "{$equipo->nombre_equipo} - {$equipo->marca} {$equipo->modelo}"
                        ];
                    } else {
                        // OTROS: Mostrar tipo + cantidad disponible
                        return [
                            'id' => $equipo->id,
                            'tipo' => $equipo->tipo,
                            'marca' => $equipo->marca,
                            'modelo' => $equipo->modelo,
                            'nombre_equipo' => $equipo->nombre_equipo,
                            'es_individual' => false,
                            'cantidad_disponible' => $equipo->cantidad_disponible,
                            'cantidad_total' => $equipo->cantidad_total,
                            'display' => "{$equipo->nombre_equipo} {$equipo->marca} - Modelo {$equipo->modelo} | Disponibles: {$equipo->cantidad_disponible}/{$equipo->cantidad_total}"
                        ];
                    }
                });

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
     * Obtiene los estados actuales de todos los equipos del inventario.
     *
     * Endpoint utilizado por el sistema de polling en tiempo real del frontend.
     * Retorna solo `id` y `estado` de cada equipo para minimizar la carga de datos.
     * El frontend actualiza los indicadores de estado sin recargar la página completa.
     *
     * @return \Illuminate\Http\JsonResponse Lista de equipos con su id y estado actual.
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