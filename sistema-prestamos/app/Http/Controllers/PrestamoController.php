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
            'equipo_cantidad' => 'required|array|min:1',  // NUEVO: Array de cantidades
            'equipo_cantidad.*' => 'required|integer|min:1',  // NUEVO: Validar cada cantidad
            'practicante_id' => 'required|exists:estudiantes,id',
            'fecha_devolucion_esperada' => 'required|date|after_or_equal:today',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'fecha_devolucion_esperada.after_or_equal' => 'La fecha de devolución no puede ser anterior al día de hoy.',
            'equipo_id.required' => 'Debes seleccionar al menos un equipo.',
            'equipo_cantidad.required' => 'Debes especificar la cantidad para cada equipo.',
        ]);

        // ===================================================================
        // VALIDACIÓN PREVIA: Verificar disponibilidad de TODOS los equipos
        // ===================================================================
        foreach ($request->equipo_id as $index => $idEquipo) {
            $equipo = Equipo::find($idEquipo);
            $cantidadSolicitada = $request->equipo_cantidad[$index];

            // Verificar que el equipo existe y está disponible
            if ($equipo->estado !== 'disponible') {
                return redirect()
                    ->back()
                    ->withErrors([
                        'equipo_id' => "El equipo '{$equipo->nombre_equipo}' (ID: {$equipo->id}) ya no está disponible."
                    ])
                    ->withInput();
            }

            // VALIDAR CANTIDAD para equipos no individuales
            if (!$equipo->es_individual) {
                if ($cantidadSolicitada > $equipo->cantidad_disponible) {
                    return redirect()
                        ->back()
                        ->withErrors([
                            'equipo_cantidad' => "Solo hay {$equipo->cantidad_disponible} unidad(es) disponible(s) de '{$equipo->nombre_equipo}'. Solicitaste {$cantidadSolicitada}."
                        ])
                        ->withInput();
                }
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
                $datos['cantidades'] = $request->equipo_cantidad;  // NUEVO: Pasar cantidades
                $this->prestamoService->actualizarPrestamo($prestamoActivo, $datos);

                return redirect()->route('dashboard')
                    ->with('success', "✅ Préstamo actualizado correctamente.\n\n" .
                        "• Se agregaron {$cantidadNuevos} equipo(s) nuevo(s)\n" .
                        "• Total de equipos en préstamo: " . count($request->equipo_id) . "\n" .
                        "• Préstamo ID: #{$prestamoActivo->id}");
            } else {
                // ===============================================================
                // CASO: EL ESTUDIANTE NO TIENE PRÉSTAMOS ACTIVOS
                // Crear un nuevo préstamo
                // ===============================================================

                $datos['cantidades'] = $request->equipo_cantidad;  // NUEVO: Pasar cantidades
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
            'equipos_nuevos_cantidad' => 'nullable|array',  // NUEVO: Cantidades para equipos nuevos
            'equipos_nuevos_cantidad.*' => 'nullable|integer|min:1',  // NUEVO
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

        // =======================================
        // Combinar IDs (actuales + nuevos)
        // ====================================
        $todosLosEquipos = array_merge($equiposActualesMarcados, $equiposNuevosAgregados);
        $todosLosEquipos = array_values($todosLosEquipos); // Reindexar

        // Validar que haya al menos 1 equipo
        if (empty($todosLosEquipos)) {
            return redirect()
                ->back()
                ->withErrors(['equipo_id' => 'El préstamo no puede quedar vacío.'])
                ->withInput();
        }

        // ===================================================================
        // CREAR ARRAY DE CANTIDADES
        // - Equipos actuales: cantidad = 1 (ya están prestados, no necesitan cantidad)
        // - Equipos nuevos: usar la cantidad especificada o 1 por defecto
        // ===================================================================
        $cantidades = [];

        // Para equipos actuales marcados: cantidad = 1 (no se usa realmente, solo es placeholder)
        foreach ($equiposActualesMarcados as $equipoId) {
            $cantidades[] = 1;
        }

        // Para equipos nuevos: usar cantidad especificada
        $cantidadesNuevos = $request->equipos_nuevos_cantidad ?? [];
        foreach ($equiposNuevosAgregados as $index => $equipoId) {
            $cantidades[] = $cantidadesNuevos[$index] ?? 1;
        }

        // ===================================================================
        // VALIDAR DISPONIBILIDAD DE EQUIPOS NUEVOS
        // Solo validar los equipos que NO están ya en el préstamo
        // ===================================================================
        $equiposYaEnPrestamo = $prestamo->prestamoEquipos()
            ->where('estado', 'activo')
            ->pluck('equipo_id')
            ->toArray();

        // Validar solo los nuevos
        foreach ($equiposNuevosAgregados as $index => $equipoId) {
            // Si ya está en el préstamo, no validar
            if (in_array($equipoId, $equiposYaEnPrestamo)) {
                continue;
            }

            $cantidad = $cantidadesNuevos[$index] ?? 1;
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
            $datos['cantidades'] = $cantidades;  // NUEVO: Pasar cantidades

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
            'equipos_devolver.*' => 'exists:prestamo_equipos,id',  // CAMBIADO: Ahora valida IDs de prestamo_equipos
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
            // Si no se especifican equipos, devolver TODOS los activos del préstamo
            $prestamoEquiposIds = $request->equipos_devolver;

            if (empty($prestamoEquiposIds)) {
                // Si no se marca ninguno, obtener todos los prestamo_equipo activos
                $prestamoEquiposIds = $prestamo->prestamoEquipos()
                    ->where('estado', 'activo')
                    ->pluck('id')  // CAMBIADO: Obtener IDs de prestamo_equipos, no equipo_id
                    ->toArray();
            }

            // Preparar datos para el servicio
            $datos = [
                'practicante_recibe_id' => $request->practicante_recibe_id,
                'observaciones_devolucion' => $request->observaciones_devolucion,
            ];

            // Pasar los IDs de prestamo_equipos al servicio
            $this->prestamoService->registrarDevolucion(
                $prestamo,
                $prestamoEquiposIds,  // array de IDs de prestamo_equipos
                $datos                // array con practicante_recibe_id y observaciones
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

        // Generar CSV
        $nombreArchivo = 'reporte_prestamos_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($prestamos) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM para Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados
            fputcsv($file, [
                'ID Préstamo',
                'Tipo de Equipo',
                'Marca',
                'Modelo',
                'Código Equipo',
                'Estudiante Solicitante',
                'Carrera',
                'Practicante que Registra',
                'Fecha Préstamo',
                'Hora Préstamo',
                'Fecha Esperada Devolución',
                'Fecha Real Devolución',
                'Hora Real Devolución',
                'Practicante que Recibe',
                'Estado',
                'Cumplimiento',
                'Tiempo de Préstamo',
                'Observaciones Préstamo',
                'Observaciones Devolución',
            ], ';');

            // Datos - Una fila por cada equipo en el préstamo
            foreach ($prestamos as $prestamo) {
                foreach ($prestamo->prestamoEquipos as $prestamoEquipo) {
                    $equipo = $prestamoEquipo->equipo;

                    // Calcular cumplimiento basado en este equipo específico
                    $cumplimiento = 'Pendiente';
                    $fechaEsperada = Carbon::parse($prestamo->fecha_devolucion_esperada)->startOfDay();
                    $hoy = Carbon::now()->startOfDay();

                    if ($prestamoEquipo->estado == 'devuelto' && $prestamoEquipo->fecha_devolucion_real) {
                        // Equipo ya devuelto
                        $fechaDevolucionReal = Carbon::parse($prestamoEquipo->fecha_devolucion_real)->startOfDay();

                        if ($fechaDevolucionReal->lte($fechaEsperada)) {
                            $cumplimiento = 'Listo';
                        } else {
                            $diasRetraso = $fechaEsperada->diffInDays($fechaDevolucionReal);
                            $cumplimiento = "Listo (Atraso +{$diasRetraso} días)";
                        }
                    } else {
                        // Equipo aún no devuelto (activo)
                        if ($hoy->gt($fechaEsperada)) {
                            $diasRetraso = $fechaEsperada->diffInDays($hoy);
                            $cumplimiento = "Atrasado (+{$diasRetraso} días)";
                        } else {
                            $cumplimiento = 'En Curso';
                        }
                    }

                    // Calcular tiempo de préstamo
                    $tiempoPrestamo = '-';
                    if ($prestamoEquipo->estado == 'devuelto' && $prestamoEquipo->fecha_devolucion_real) {
                        $inicio = Carbon::parse($prestamo->fecha_prestamo);
                        $fin = Carbon::parse($prestamoEquipo->fecha_devolucion_real);

                        $minutosTotales = floor($inicio->diffInMinutes($fin));
                        $horasTotales = floor($inicio->diffInHours($fin));
                        $diasTotales = floor($inicio->diffInDays($fin));

                        if ($minutosTotales < 1) {
                            $tiempoPrestamo = 'Menos de 1 minuto';
                        } elseif ($minutosTotales == 1) {
                            $tiempoPrestamo = '1 minuto';
                        } elseif ($minutosTotales < 60) {
                            $tiempoPrestamo = $minutosTotales . ' minutos';
                        } elseif ($horasTotales == 1) {
                            $tiempoPrestamo = '1 hora';
                        } elseif ($horasTotales < 24) {
                            $tiempoPrestamo = $horasTotales . ' horas';
                        } elseif ($diasTotales == 1) {
                            $tiempoPrestamo = '1 día';
                        } else {
                            $tiempoPrestamo = $diasTotales . ' días';
                        }
                    }

                    fputcsv($file, [
                        $prestamo->id,
                        $equipo->tipo ?? '-',
                        $equipo->marca ?? '-',
                        $equipo->modelo ?? '-',
                        $equipo->nombre_equipo ?? '-',
                        ($prestamo->estudiante->nombre ?? '') . ' ' . ($prestamo->estudiante->apellido ?? ''),
                        $prestamo->estudiante->carrera ?? '-',
                        ($prestamo->practicante->nombre ?? '') . ' ' . ($prestamo->practicante->apellido ?? ''),
                        Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y'),
                        Carbon::parse($prestamo->fecha_prestamo)->format('H:i A'),
                        $fechaEsperada->format('d/m/Y'),
                        $prestamoEquipo->fecha_devolucion_real ? Carbon::parse($prestamoEquipo->fecha_devolucion_real)->format('d/m/Y') : 'Pendiente',
                        $prestamoEquipo->fecha_devolucion_real ? Carbon::parse($prestamoEquipo->fecha_devolucion_real)->format('H:i A') : '-',
                        $prestamoEquipo->practicanteRecibe ? ($prestamoEquipo->practicanteRecibe->nombre . ' ' . $prestamoEquipo->practicanteRecibe->apellido) : '-',
                        $prestamoEquipo->estado == 'activo' ? 'En Curso' : 'Devuelto',
                        $cumplimiento,
                        $tiempoPrestamo,
                        $prestamo->observaciones_prestamo ?? '-',
                        $prestamoEquipo->observaciones_devolucion ?? '-',
                    ], ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }
}