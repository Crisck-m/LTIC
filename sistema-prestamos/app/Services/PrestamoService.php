<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\Prestamo;
use App\Models\PrestamoEquipo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PrestamoService
{
    /**
     * Listar préstamos con filtros
     */
    public function listarPrestamos($search = null, $estado = null, $fechaDesde = null, $fechaHasta = null)
    {
        $query = Prestamo::with(['estudiante', 'practicante', 'prestamoEquipos.equipo']);

        // Filtro de búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('estudiante', function ($subQ) use ($search) {
                    $subQ->where('nombre', 'LIKE', "%{$search}%")
                        ->orWhere('apellido', 'LIKE', "%{$search}%")
                        ->orWhere('cedula', 'LIKE', "%{$search}%");
                })
                    ->orWhereHas('prestamoEquipos.equipo', function ($subQ) use ($search) {
                        $subQ->where('nombre_equipo', 'LIKE', "%{$search}%")
                            ->orWhere('tipo', 'LIKE', "%{$search}%")
                            ->orWhere('marca', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Filtro de estado
        if ($estado) {
            if ($estado === 'atrasado') {
                // Préstamos activos cuya fecha esperada ya pasó
                $query->where('estado', 'activo')
                    ->where('fecha_devolucion_esperada', '<', now()->toDateString());
            } else {
                $query->where('estado', $estado);
            }
        }

        // Filtro de fechas
        if ($fechaDesde) {
            $query->whereDate('fecha_prestamo', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->whereDate('fecha_prestamo', '<=', $fechaHasta);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Registrar salida de equipo(s)
     */
    public function registrarSalida(array $datos)
    {
        return DB::transaction(function () use ($datos) {
            // Validar que sea un array de equipos
            $equiposIds = is_array($datos['equipo_id']) ? $datos['equipo_id'] : [$datos['equipo_id']];

            // Crear el préstamo principal
            $prestamo = Prestamo::create([
                'estudiante_id' => $datos['estudiante_id'],
                'practicante_id' => $datos['practicante_id'],
                'user_id' => auth()->id(),
                'fecha_prestamo' => now(),
                'fecha_devolucion_esperada' => $datos['fecha_devolucion_esperada'],
                'estado' => 'activo',
                'observaciones_prestamo' => $datos['observaciones'] ?? null,
            ]);

            // Asociar equipos al préstamo
            foreach ($equiposIds as $equipoId) {
                PrestamoEquipo::create([
                    'prestamo_id' => $prestamo->id,
                    'equipo_id' => $equipoId,
                    'estado' => 'activo',
                ]);

                // Actualizar estado del equipo
                Equipo::find($equipoId)->update(['estado' => 'prestado']);
            }

            return $prestamo;
        });
    }

    /**
     * Registrar devolución de equipo(s)
     */
    public function registrarDevolucion(Prestamo $prestamo, $observaciones = null, $practicanteRecibeId = null, $equiposIds = null)
    {
        return DB::transaction(function () use ($prestamo, $observaciones, $practicanteRecibeId, $equiposIds) {
            // Si no se especifican equipos, devolver TODOS los activos
            if (!$equiposIds) {
                $equiposIds = $prestamo->prestamoEquipos()
                    ->where('estado', 'activo')
                    ->pluck('equipo_id')
                    ->toArray();
            }

            // Marcar equipos como devueltos
            foreach ($equiposIds as $equipoId) {
                $prestamoEquipo = PrestamoEquipo::where('prestamo_id', $prestamo->id)
                    ->where('equipo_id', $equipoId)
                    ->where('estado', 'activo')
                    ->first();

                if ($prestamoEquipo) {
                    $prestamoEquipo->update([
                        'fecha_devolucion_real' => now(),
                        'practicante_recibe_id' => $practicanteRecibeId,
                        'observaciones_devolucion' => $observaciones,
                        'estado' => 'devuelto',
                    ]);

                    // Liberar el equipo
                    Equipo::find($equipoId)->update(['estado' => 'disponible']);
                }
            }

            // Verificar si todos los equipos fueron devueltos
            $equiposActivos = $prestamo->prestamoEquipos()
                ->where('estado', 'activo')
                ->count();

            // Si no quedan equipos activos, finalizar el préstamo
            if ($equiposActivos === 0) {
                $prestamo->update(['estado' => 'finalizado']);
            }

            return $prestamo;
        });
    }

    /**
     * Actualizar préstamo (agregar/quitar equipos)
     */
    public function actualizarPrestamo(Prestamo $prestamo, array $datos)
    {
        return DB::transaction(function () use ($prestamo, $datos) {
            // Actualizar datos generales del préstamo
            $prestamo->update([
                'fecha_devolucion_esperada' => $datos['fecha_devolucion_esperada'],
                'observaciones_prestamo' => $datos['observaciones'] ?? null,
            ]);

            $equiposNuevos = $datos['equipo_id'];
            $equiposActuales = $prestamo->prestamoEquipos()
                ->where('estado', 'activo')
                ->pluck('equipo_id')
                ->toArray();

            // Equipos a AGREGAR
            $equiposAgregar = array_diff($equiposNuevos, $equiposActuales);
            foreach ($equiposAgregar as $equipoId) {
                PrestamoEquipo::create([
                    'prestamo_id' => $prestamo->id,
                    'equipo_id' => $equipoId,
                    'estado' => 'activo',
                ]);

                Equipo::find($equipoId)->update(['estado' => 'prestado']);
            }

            // Equipos a QUITAR
            $equiposQuitar = array_diff($equiposActuales, $equiposNuevos);
            foreach ($equiposQuitar as $equipoId) {
                $prestamoEquipo = PrestamoEquipo::where('prestamo_id', $prestamo->id)
                    ->where('equipo_id', $equipoId)
                    ->where('estado', 'activo')
                    ->first();

                if ($prestamoEquipo) {
                    $prestamoEquipo->update([
                        'estado' => 'cancelado',
                        'observaciones_devolucion' => 'Equipo removido durante edición del préstamo',
                    ]);

                    Equipo::find($equipoId)->update(['estado' => 'disponible']);
                }
            }

            return $prestamo;
        });
    }

    /**
     * Obtener préstamos pendientes de devolución (con paginación)
     */
    public function obtenerPendientes()
    {
        return Prestamo::with(['estudiante', 'practicante', 'prestamoEquipos.equipo'])
            ->where('estado', 'activo')
            ->latest('fecha_prestamo')
            ->paginate(15);
    }
}