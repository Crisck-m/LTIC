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
            'observaciones' => 'nullable|string',
            'fecha_devolucion_esperada' => 'required|date|after_or_equal:today',
            'notificar_retorno' => 'nullable|boolean',
            'periodo_notificacion' => 'nullable|in:1_dia,1_semana,1_mes'
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

        $datos['notificar_retorno'] = $request->boolean('notificar_retorno');
        if ($datos['notificar_retorno'] && empty($datos['periodo_notificacion'])) {
            $datos['periodo_notificacion'] = '1_dia';
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
        $rolUsuario = 'Admin/Practicante'; // Simple, como solicitado

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

        // Crear CSV en memoria
        $nombreArchivo = 'reporte_prestamos_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($prestamos) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8 (para que Excel muestre correctamente los acentos)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados
            fputcsv($file, [
                'ID',
                'Tipo Equipo',
                'Equipo (Marca/Modelo)',
                'Código Equipo',
                'Estudiante Solicitante',
                'Carrera',
                'Practicante Registra',
                'Fecha y Hora Préstamo',
                'Fecha Esperada Devolución',
                'Fecha Real Devolución',
                'Practicante Recibe',
                'Estado',
                'Cumplimiento',
                'Días de Préstamo',
                'Observaciones Préstamo',
                'Observaciones Devolución',
            ]);

            // Datos
            foreach ($prestamos as $prestamo) {
                // Calcular cumplimiento
                $cumplimiento = 'Pendiente';
                $diasPrestamo = '-';

                if ($prestamo->estado == 'finalizado' && $prestamo->fecha_devolucion_real) {
                    $fechaReal = Carbon::parse($prestamo->fecha_devolucion_real)->startOfDay();
                    $fechaEsperada = Carbon::parse($prestamo->fecha_devolucion_esperada)->startOfDay();
                    $fechaPrestamo = Carbon::parse($prestamo->fecha_prestamo);

                    $diasPrestamo = $fechaPrestamo->diffInDays($fechaReal);

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
                    ($prestamo->equipo->marca ?? '-') . ' / ' . ($prestamo->equipo->modelo ?? '-'),
                    $prestamo->equipo->nombre_equipo ?? '-',
                    ($prestamo->estudiante->nombre ?? '') . ' ' . ($prestamo->estudiante->apellido ?? ''),
                    $prestamo->estudiante->carrera ?? '-',
                    ($prestamo->practicante->nombre ?? '') . ' ' . ($prestamo->practicante->apellido ?? ''),
                    $prestamo->fecha_prestamo ? Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y H:i') : '-',
                    $prestamo->fecha_devolucion_esperada ? Carbon::parse($prestamo->fecha_devolucion_esperada)->format('d/m/Y') : '-',
                    $prestamo->fecha_devolucion_real ? Carbon::parse($prestamo->fecha_devolucion_real)->format('d/m/Y H:i') : 'Pendiente',
                    $prestamo->practicanteDevolucion ? ($prestamo->practicanteDevolucion->nombre . ' ' . $prestamo->practicanteDevolucion->apellido) : 'Pendiente',
                    $prestamo->estado == 'activo' ? 'En Curso' : 'Devuelto',
                    $cumplimiento,
                    $diasPrestamo,
                    $prestamo->observaciones_prestamo ?? '-',
                    $prestamo->observaciones_devolucion ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}