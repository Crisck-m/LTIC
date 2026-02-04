<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Estudiante;
use App\Models\Prestamo;
use App\Services\PrestamoService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

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
            'fecha_devolucion_esperada' => 'required|date|after_or_equal:today',
            'observaciones' => 'nullable|string|max:500',
            'periodo_notificacion' => 'nullable|in:1_dia,1_semana,1_mes',
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

    public function exportPDF(Request $request)
    {
        // Validar filtros
        $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        // Obtener préstamos filtrados (sin paginación)
        $query = Prestamo::with(['equipo', 'estudiante', 'practicante', 'practicanteDevolucion']);

        // Aplicar filtros
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('estudiante', function ($subQ) use ($request) {
                    $subQ->where('nombre', 'LIKE', "%{$request->search}%")
                        ->orWhere('apellido', 'LIKE', "%{$request->search}%");
                })->orWhereHas('equipo', function ($subQ) use ($request) {
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
            'a_tiempo' => $prestamos->filter(function ($p) {
                if ($p->estado == 'finalizado' && $p->fecha_devolucion_real) {
                    $fechaReal = Carbon::parse($p->fecha_devolucion_real)->startOfDay();
                    $fechaEsperada = Carbon::parse($p->fecha_devolucion_esperada)->startOfDay();
                    return $fechaReal->lte($fechaEsperada);
                }
                return false;
            })->count(),
            'con_retraso' => $prestamos->filter(function ($p) {
                if ($p->estado == 'finalizado' && $p->fecha_devolucion_real) {
                    $fechaReal = Carbon::parse($p->fecha_devolucion_real)->startOfDay();
                    $fechaEsperada = Carbon::parse($p->fecha_devolucion_esperada)->startOfDay();
                    return $fechaReal->gt($fechaEsperada);
                }
                return false;
            })->count(),
        ];

        // Preparar datos para la vista
        $fechaGeneracion = Carbon::now()->format('d/m/Y');
        $horaGeneracion = Carbon::now()->format('H:i A');
        $rolUsuario = auth()->user()->name; // Nombre del usuario que genera el reporte

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
        $query = Prestamo::with(['equipo', 'estudiante', 'practicante', 'practicanteDevolucion']);

        // Aplicar filtros (misma lógica que PDF)
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('estudiante', function ($subQ) use ($request) {
                    $subQ->where('nombre', 'LIKE', "%{$request->search}%")
                        ->orWhere('apellido', 'LIKE', "%{$request->search}%");
                })->orWhereHas('equipo', function ($subQ) use ($request) {
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
        $total = $prestamos->count();
        $activos = $prestamos->where('estado', 'activo')->count();
        $finalizados = $prestamos->where('estado', 'finalizado')->count();
        $aTiempo = $prestamos->filter(function ($p) {
            if ($p->estado == 'finalizado' && $p->fecha_devolucion_real) {
                $fechaReal = Carbon::parse($p->fecha_devolucion_real)->startOfDay();
                $fechaEsperada = Carbon::parse($p->fecha_devolucion_esperada)->startOfDay();
                return $fechaReal->lte($fechaEsperada);
            }
            return false;
        })->count();
        $conRetraso = $prestamos->filter(function ($p) {
            if ($p->estado == 'finalizado' && $p->fecha_devolucion_real) {
                $fechaReal = Carbon::parse($p->fecha_devolucion_real)->startOfDay();
                $fechaEsperada = Carbon::parse($p->fecha_devolucion_esperada)->startOfDay();
                return $fechaReal->gt($fechaEsperada);
            }
            return false;
        })->count();

        // Preparar metadata
        $fechaGeneracion = Carbon::now()->format('d/m/Y');
        $horaGeneracion = Carbon::now()->format('H:i A');
        $usuarioGenerador = auth()->user()->name;

        // Crear CSV en memoria
        $nombreArchivo = 'reporte_prestamos_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($prestamos, $fechaGeneracion, $horaGeneracion, $usuarioGenerador, $total, $activos, $finalizados, $aTiempo, $conRetraso, $request) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8 (para que Excel muestre correctamente los acentos)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // USAR PUNTO Y COMA COMO DELIMITADOR (estándar Excel en español)
            $delimiter = ';';

            // === SECCIÓN DE METADATA ===
            fputcsv($file, ['SISTEMA DE PRÉSTAMOS LTIC'], $delimiter);
            fputcsv($file, ['Reporte de Préstamos'], $delimiter);
            fputcsv($file, [], $delimiter); // Línea vacía

            fputcsv($file, ['Fecha de Generación:', $fechaGeneracion], $delimiter);
            fputcsv($file, ['Hora de Generación:', $horaGeneracion], $delimiter);
            fputcsv($file, ['Generado por:', $usuarioGenerador], $delimiter);
            fputcsv($file, [], $delimiter); // Línea vacía

            // Filtros aplicados
            if ($request->search || $request->estado || $request->fecha_desde || $request->fecha_hasta) {
                fputcsv($file, ['FILTROS APLICADOS:'], $delimiter);
                if ($request->search) {
                    fputcsv($file, ['Búsqueda:', $request->search], $delimiter);
                }
                if ($request->estado) {
                    fputcsv($file, ['Estado:', $request->estado == 'activo' ? 'En Curso' : 'Devueltos'], $delimiter);
                }
                if ($request->fecha_desde) {
                    fputcsv($file, ['Desde:', Carbon::parse($request->fecha_desde)->format('d/m/Y')], $delimiter);
                }
                if ($request->fecha_hasta) {
                    fputcsv($file, ['Hasta:', Carbon::parse($request->fecha_hasta)->format('d/m/Y')], $delimiter);
                }
            } else {
                fputcsv($file, ['FILTROS APLICADOS:', 'Sin filtros (Reporte completo)'], $delimiter);
            }

            fputcsv($file, ['Total de Registros:', $total . ' préstamo(s)'], $delimiter);
            fputcsv($file, [], $delimiter); // Línea vacía
            fputcsv($file, [], $delimiter); // Línea vacía

            // === ENCABEZADOS DE LA TABLA ===
            fputcsv($file, [
                'ID Préstamo',
                'Tipo Equipo',
                'Marca',
                'Modelo',
                'Código Equipo',
                'Estudiante',
                'Carrera',
                'Practicante Registra',
                'Fecha Préstamo',
                'Hora Préstamo',
                'Fecha Esperada Devolución',
                'Fecha Real Devolución',
                'Hora Real Devolución',
                'Practicante Recibe',
                'Estado',
                'Cumplimiento',
                'Tiempo de Préstamo',
                'Observaciones Préstamo',
                'Observaciones Devolución',
            ], $delimiter);

            // === DATOS ===
            foreach ($prestamos as $prestamo) {
                // Calcular cumplimiento
                $cumplimiento = 'Pendiente';
                $tiempoPrestamo = '-';

                if ($prestamo->estado == 'finalizado' && $prestamo->fecha_devolucion_real) {
                    $fechaReal = Carbon::parse($prestamo->fecha_devolucion_real)->startOfDay();
                    $fechaEsperada = Carbon::parse($prestamo->fecha_devolucion_esperada)->startOfDay();

                    // Calcular tiempo de préstamo (con horas exactas)
                    $inicio = Carbon::parse($prestamo->fecha_prestamo);
                    $fin = Carbon::parse($prestamo->fecha_devolucion_real);

                    // Redondear a enteros para evitar decimales
                    $minutosTotales = floor($inicio->diffInMinutes($fin));
                    $horasTotales = floor($inicio->diffInHours($fin));
                    $diasTotales = floor($inicio->diffInDays($fin));

                    // Formatear según la duración con gramática correcta
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

                    // Calcular cumplimiento (solo comparar fechas)
                    if ($fechaReal->lte($fechaEsperada)) {
                        $cumplimiento = 'A tiempo';
                    } else {
                        $diasRetraso = $fechaEsperada->diffInDays($fechaReal);
                        $cumplimiento = "Con retraso (+{$diasRetraso} días)";
                    }
                }

                fputcsv($file, [
                    $prestamo->id,
                    $prestamo->equipo->tipo ?? '-',
                    $prestamo->equipo->marca ?? '-',
                    $prestamo->equipo->modelo ?? '-',
                    $prestamo->equipo->nombre_equipo ?? '-',
                    ($prestamo->estudiante->nombre ?? '') . ' ' . ($prestamo->estudiante->apellido ?? ''),
                    $prestamo->estudiante->carrera ?? '-',
                    ($prestamo->practicante->nombre ?? '') . ' ' . ($prestamo->practicante->apellido ?? ''),
                    $prestamo->fecha_prestamo ? Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y') : '-',
                    $prestamo->fecha_prestamo ? Carbon::parse($prestamo->fecha_prestamo)->format('H:i') : '-',
                    $prestamo->fecha_devolucion_esperada ? Carbon::parse($prestamo->fecha_devolucion_esperada)->format('d/m/Y') : '-',
                    $prestamo->fecha_devolucion_real ? Carbon::parse($prestamo->fecha_devolucion_real)->format('d/m/Y') : 'Pendiente',
                    $prestamo->fecha_devolucion_real ? Carbon::parse($prestamo->fecha_devolucion_real)->format('H:i') : '-',
                    $prestamo->practicanteDevolucion ? ($prestamo->practicanteDevolucion->nombre . ' ' . $prestamo->practicanteDevolucion->apellido) : 'Pendiente',
                    $prestamo->estado == 'activo' ? 'En Curso' : 'Devuelto',
                    $cumplimiento,
                    $tiempoPrestamo,
                    $prestamo->observaciones_prestamo ?? '-',
                    $prestamo->observaciones_devolucion ?? '-',
                ], $delimiter);
            }

            // === SECCIÓN DE ESTADÍSTICAS ===
            fputcsv($file, [], $delimiter); // Línea vacía
            fputcsv($file, [], $delimiter); // Línea vacía
            fputcsv($file, ['RESUMEN ESTADÍSTICO'], $delimiter);
            fputcsv($file, ['Total de Préstamos:', $total], $delimiter);
            fputcsv($file, ['Préstamos Activos:', $activos], $delimiter);
            fputcsv($file, ['Préstamos Finalizados:', $finalizados], $delimiter);
            fputcsv($file, ['Devueltos a Tiempo:', $aTiempo], $delimiter);
            fputcsv($file, ['Devueltos con Retraso:', $conRetraso], $delimiter);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}