<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Estudiante;
use App\Models\Prestamo;
use App\Services\PrestamoService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            'equipo_id' => 'required|array|min:1',
            'equipo_id.*' => 'exists:equipos,id',
            'practicante_id' => 'required|exists:estudiantes,id',
            'fecha_devolucion_esperada' => 'required|date|after_or_equal:today',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'fecha_devolucion_esperada.after_or_equal' => 'La fecha de devolución no puede ser anterior al día de hoy.',
            'equipo_id.required' => 'Debes seleccionar al menos un equipo.',
        ]);

        // Validación previa: verificar que TODOS los equipos están disponibles
        foreach ($request->equipo_id as $idEquipo) {
            $equipo = Equipo::find($idEquipo);
            if ($equipo->estado !== 'disponible') {
                return redirect()
                    ->back()
                    ->withErrors([
                        'equipo_id' => "El equipo '{$equipo->nombre_equipo}' (ID: {$equipo->id}) ya no está disponible."
                    ])
                    ->withInput();
            }
        }

        try {
            // ===================================================================
            // VERIFICAR SI EL ESTUDIANTE YA TIENE UN PRÉSTAMO ACTIVO
            // ===================================================================
            $prestamoActivo = Prestamo::where('estudiante_id', $datos['estudiante_id'])
                ->where('estado', 'activo')
                ->first();

            if ($prestamoActivo) {
                // ===============================================================
                // CASO: EL ESTUDIANTE YA TIENE UN PRÉSTAMO ACTIVO
                // Actualizar el préstamo existente agregando los nuevos equipos
                // ===============================================================

                // Obtener equipos actuales del préstamo
                $equiposActuales = $prestamoActivo->prestamoEquipos()
                    ->where('estado', 'activo')
                    ->pluck('equipo_id')
                    ->toArray();

                // Combinar equipos actuales con los nuevos (sin duplicados)
                $todosLosEquipos = array_unique(array_merge($equiposActuales, $datos['equipo_id']));

                // Contar cuántos equipos nuevos se están agregando
                $equiposNuevos = array_diff($datos['equipo_id'], $equiposActuales);
                $cantidadNuevos = count($equiposNuevos);

                // Actualizar el array de equipos
                $datos['equipo_id'] = array_values($todosLosEquipos);

                // Actualizar fecha de devolución esperada si es posterior a la actual
                $fechaActual = \Carbon\Carbon::parse($prestamoActivo->fecha_devolucion_esperada);
                $fechaNueva = \Carbon\Carbon::parse($datos['fecha_devolucion_esperada']);

                if ($fechaNueva->greaterThan($fechaActual)) {
                    $prestamoActivo->fecha_devolucion_esperada = $datos['fecha_devolucion_esperada'];
                }

                // Actualizar observaciones (concatenar si hay nuevas)
                if (!empty($datos['observaciones'])) {
                    $observacionActual = $prestamoActivo->observaciones_prestamo ?? '';
                    $prestamoActivo->observaciones_prestamo = trim($observacionActual . "\n[Actualización] " . $datos['observaciones']);
                }

                $prestamoActivo->save();

                // Usar el servicio para actualizar los equipos
                $this->prestamoService->actualizarPrestamo($prestamoActivo, $datos);

                return redirect()->route('dashboard')
                    ->with('success', "✅ Préstamo actualizado correctamente.\n\n" .
                        "• Se agregaron {$cantidadNuevos} equipo(s) nuevo(s)\n" .
                        "• Total de equipos en préstamo: " . count($todosLosEquipos) . "\n" .
                        "• Préstamo ID: #{$prestamoActivo->id}");
            } else {
                // ===============================================================
                // CASO: EL ESTUDIANTE NO TIENE PRÉSTAMOS ACTIVOS
                // Crear un nuevo préstamo
                // ===============================================================

                $prestamo = $this->prestamoService->registrarSalida($datos);

                return redirect()->route('dashboard')
                    ->with('success', "✅ Préstamo registrado correctamente.\n\n" .
                        "• Equipos prestados: " . count($request->equipo_id) . "\n" .
                        "• Préstamo ID: #{$prestamo->id}");
            }
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Error al procesar el préstamo: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(Prestamo $prestamo)
    {
        if ($prestamo->estado !== 'activo') {
            return redirect()->back()->with('error', 'Solo se pueden editar préstamos activos.');
        }

        $prestamo->load(['estudiante', 'practicante', 'prestamoEquipos.equipo']);

        return view('prestamos.edit', compact('prestamo'));
    }

    public function update(Request $request, Prestamo $prestamo)
    {
        // Validar datos del formulario
        $datos = $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'practicante_id' => 'required|exists:estudiantes,id',
            'equipo_id' => 'nullable|array', // Equipos marcados (actuales)
            'equipo_id.*' => 'exists:equipos,id',
            'equipos_nuevos' => 'nullable|array', // Equipos nuevos agregados
            'equipos_nuevos.*' => 'exists:equipos,id',
            'fecha_devolucion_esperada' => 'required|date|after_or_equal:today',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'fecha_devolucion_esperada.after_or_equal' => 'La fecha de devolución no puede ser anterior al día de hoy.',
        ]);

        // CRÍTICO: Combinar equipos actuales marcados + equipos nuevos
        $equiposActualesMarcados = $request->equipo_id ?? [];
        $equiposNuevosAgregados = $request->equipos_nuevos ?? [];

        // Filtrar valores vacíos
        $equiposActualesMarcados = array_filter($equiposActualesMarcados);
        $equiposNuevosAgregados = array_filter($equiposNuevosAgregados);

        // ===================================================================
        // COMBINAR PERMITIENDO DUPLICADOS PARA EQUIPOS POR CANTIDAD
        // ===================================================================
        $todosLosEquipos = array_merge($equiposActualesMarcados, $equiposNuevosAgregados);

        // NO usar array_unique() aquí - permite múltiples unidades del mismo equipo
        // Para laptops individuales, el ID es único de todas formas
        // Para equipos por cantidad (Mouse, etc.), cada ID representa una unidad adicional

        $todosLosEquipos = array_values($todosLosEquipos); // Solo reindexar

        // Validar que haya al menos 1 equipo
        if (empty($todosLosEquipos)) {
            return redirect()
                ->back()
                ->withErrors(['equipo_id' => 'El préstamo no puede quedar vacío.'])
                ->withInput();
        }

        // ===================================================================
        // VALIDAR DISPONIBILIDAD DE EQUIPOS NUEVOS
        // Solo validar los equipos que NO están ya en el préstamo
        // ===================================================================
        $equiposYaEnPrestamo = $prestamo->prestamoEquipos()
            ->where('estado', 'activo')
            ->pluck('equipo_id')
            ->toArray();

        // Contar cuántas veces aparece cada equipo nuevo
        $equiposNuevosCounts = array_count_values($equiposNuevosAgregados);

        foreach ($equiposNuevosCounts as $equipoId => $cantidad) {
            // Si ya está en el préstamo, no validar (ya lo tiene)
            if (in_array($equipoId, $equiposYaEnPrestamo)) {
                continue;
            }

            // Validar disponibilidad
            $equipo = Equipo::find($equipoId);

            if (!$equipo) {
                return redirect()
                    ->back()
                    ->withErrors(['equipo_id' => "El equipo con ID {$equipoId} no existe."])
                    ->withInput();
            }

            if ($equipo->es_individual) {
                // LAPTOP: Solo verificar que esté disponible
                if ($equipo->estado !== 'disponible') {
                    return redirect()
                        ->back()
                        ->withErrors([
                            'equipo_id' => "El equipo '{$equipo->nombre_equipo}' no está disponible."
                        ])
                        ->withInput();
                }
            } else {
                // EQUIPOS POR CANTIDAD: Verificar que haya suficiente stock
                if ($equipo->cantidad_disponible < $cantidad) {
                    return redirect()
                        ->back()
                        ->withErrors([
                            'equipo_id' => "No hay suficiente stock de '{$equipo->nombre_equipo}'. Solicitados: {$cantidad}, Disponibles: {$equipo->cantidad_disponible}"
                        ])
                        ->withInput();
                }
            }
        }

        try {
            // Pasar el array combinado (CON duplicados permitidos) al service
            $datos['equipo_id'] = $todosLosEquipos;

            $this->prestamoService->actualizarPrestamo($prestamo, $datos);

            $cantidadNuevos = count($equiposNuevosAgregados);
            $mensaje = $cantidadNuevos > 0
                ? "Préstamo actualizado correctamente. Se agregaron {$cantidadNuevos} equipo(s) nuevo(s)."
                : 'Préstamo actualizado correctamente.';

            return redirect()->route('prestamos.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar préstamo: ' . $e->getMessage());
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
            'practicante_recibe_id' => 'required|exists:estudiantes,id',
            'observaciones_devolucion' => 'nullable|string',
            'equipos_devolver' => 'nullable|array',
            'equipos_devolver.*' => 'exists:equipos,id',
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
            // Si no se especifican equipos, devolver TODOS los activos
            $equiposDevolver = $request->equipos_devolver;

            if (empty($equiposDevolver)) {
                $equiposDevolver = $prestamo->prestamoEquipos()
                    ->where('estado', 'activo')
                    ->pluck('equipo_id')
                    ->toArray();
            }

            // Preparar datos para el servicio
            $datos = [
                'practicante_recibe_id' => $request->practicante_recibe_id,
                'observaciones_devolucion' => $request->observaciones_devolucion,
            ];

            // ✅ ORDEN CORRECTO DE PARÁMETROS
            $this->prestamoService->registrarDevolucion(
                $prestamo,
                $equiposDevolver,  // array de IDs de equipos
                $datos             // array con practicante_recibe_id y observaciones
            );

            return redirect()->route('prestamos.index')
                ->with('success', 'Equipo(s) devuelto(s) correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function exportPDF(Request $request)
    {
        // Validar filtros
        $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        // Obtener préstamos filtrados (sin paginación)
        $query = Prestamo::with(['estudiante', 'practicante', 'prestamoEquipos.equipo', 'prestamoEquipos.practicanteRecibe']);

        // Aplicar filtros
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('estudiante', function ($subQ) use ($request) {
                    $subQ->where('nombre', 'LIKE', "%{$request->search}%")
                        ->orWhere('apellido', 'LIKE', "%{$request->search}%");
                })->orWhereHas('prestamoEquipos.equipo', function ($subQ) use ($request) {
                    $subQ->where('nombre_equipo', 'LIKE', "%{$request->search}%")
                        ->orWhere('tipo', 'LIKE', "%{$request->search}%");
                });
            });
        }

        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        if ($request->fecha_desde) {
            $query->whereDate('fecha_prestamo', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->whereDate('fecha_prestamo', '<=', $request->fecha_hasta);
        }

        $prestamos = $query->latest()->get();

        // Calcular estadísticas
        $estadisticas = [
            'total' => $prestamos->count(),
            'activos' => $prestamos->where('estado', 'activo')->count(),
            'finalizados' => $prestamos->where('estado', 'finalizado')->count(),
            'a_tiempo' => 0,
            'con_retraso' => 0,
        ];

        // Preparar datos para la vista
        $fechaGeneracion = Carbon::now()->format('d/m/Y');
        $horaGeneracion = Carbon::now()->format('H:i A');
        $rolUsuario = auth()->user()->name;

        $filtrosAplicados = $request->search || $request->estado || $request->fecha_desde || $request->fecha_hasta;
        $filtros = [
            'search' => $request->search,
            'estado' => $request->estado,
            'fecha_desde' => $request->fecha_desde,
            'fecha_hasta' => $request->fecha_hasta,
        ];

        // Generar PDF
        $pdf = Pdf::loadView('prestamos.reports.pdf', compact(
            'prestamos',
            'estadisticas',
            'fechaGeneracion',
            'horaGeneracion',
            'rolUsuario',
            'filtrosAplicados',
            'filtros'
        ));

        $pdf->setPaper('a4', 'landscape');

        $nombreArchivo = 'reporte_prestamos_' . Carbon::now()->format('Y-m-d_His') . '.pdf';
        return $pdf->download($nombreArchivo);
    }

    public function exportExcel(Request $request)
    {
        // Similar al PDF pero para Excel...
        // (código similar al anterior, adaptado para CSV)
    }
}