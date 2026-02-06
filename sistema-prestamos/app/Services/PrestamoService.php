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
            // Crear el préstamo principal
            $prestamo = Prestamo::create([
                'estudiante_id' => $datos['estudiante_id'],
                'practicante_id' => $datos['practicante_id'],
                'fecha_prestamo' => now(),
                'fecha_devolucion_esperada' => $datos['fecha_devolucion_esperada'],
                'observaciones_prestamo' => $datos['observaciones'] ?? null,
                'estado' => 'activo',
            ]);

            // Registrar equipos en la tabla intermedia
            foreach ($datos['equipo_id'] as $equipoId) {
                $equipo = Equipo::findOrFail($equipoId);

                // ===================================================================
                // DECREMENTAR CANTIDAD DISPONIBLE
                // ===================================================================
                if ($equipo->es_individual) {
                    // LAPTOP: Cambiar estado a 'prestado'
                    $equipo->estado = 'prestado';
                    $equipo->cantidad_disponible = 0;
                } else {
                    // OTROS: Decrementar cantidad
                    if ($equipo->cantidad_disponible <= 0) {
                        throw new \Exception("No hay stock disponible de {$equipo->nombre_equipo}");
                    }
                    $equipo->cantidad_disponible -= 1;

                    // Si se agotó el stock, cambiar estado
                    if ($equipo->cantidad_disponible == 0) {
                        $equipo->estado = 'prestado';
                    }
                }
                $equipo->save();

                // Crear registro en prestamo_equipos
                PrestamoEquipo::create([
                    'prestamo_id' => $prestamo->id,
                    'equipo_id' => $equipoId,
                    'estado' => 'activo',
                ]);
            }

            return $prestamo;
        });
    }

    /**
     * Registrar devolución de equipo(s)
     */
    public function registrarDevolucion(Prestamo $prestamo, array $equiposDevueltos, array $datos)
    {
        return DB::transaction(function () use ($prestamo, $equiposDevueltos, $datos) {
            foreach ($equiposDevueltos as $equipoId) {
                // Buscar el registro específico en prestamo_equipos
                $prestamoEquipo = PrestamoEquipo::where('prestamo_id', $prestamo->id)
                    ->where('equipo_id', $equipoId)
                    ->where('estado', 'activo')
                    ->firstOrFail();

                // Actualizar el registro de devolución
                $prestamoEquipo->update([
                    'fecha_devolucion_real' => now(),
                    'practicante_recibe_id' => $datos['practicante_recibe_id'],
                    'observaciones_devolucion' => $datos['observaciones_devolucion'] ?? null,
                    'estado' => 'devuelto',
                ]);

                // ===================================================================
                // INCREMENTAR CANTIDAD DISPONIBLE
                // ===================================================================
                $equipo = Equipo::findOrFail($equipoId);

                if ($equipo->es_individual) {
                    // LAPTOP: Cambiar estado a 'disponible'
                    $equipo->estado = 'disponible';
                    $equipo->cantidad_disponible = 1;
                } else {
                    // OTROS: Incrementar cantidad
                    $equipo->cantidad_disponible += 1;

                    // Si vuelve a haber stock, cambiar estado a disponible
                    if ($equipo->cantidad_disponible > 0) {
                        $equipo->estado = 'disponible';
                    }
                }
                $equipo->save();
            }

            // Verificar si todos los equipos fueron devueltos
            $equiposPendientes = PrestamoEquipo::where('prestamo_id', $prestamo->id)
                ->where('estado', 'activo')
                ->count();

            if ($equiposPendientes === 0) {
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
            // Obtener equipos actuales del préstamo (activos)
            $equiposActuales = $prestamo->prestamoEquipos()
                ->where('estado', 'activo')
                ->pluck('equipo_id')
                ->toArray();

            $equiposNuevos = $datos['equipo_id'];

            // ===================================================================
            // CONTAR CANTIDADES DE CADA EQUIPO (maneja duplicados correctamente)
            // ===================================================================
            $countActuales = array_count_values($equiposActuales);
            $countNuevos = array_count_values($equiposNuevos);

            // Obtener todos los IDs únicos (actuales + nuevos)
            $todosIds = array_unique(array_merge(
                array_keys($countActuales),
                array_keys($countNuevos)
            ));

            // ===================================================================
            // PROCESAR CADA EQUIPO SEGÚN LA DIFERENCIA DE CANTIDADES
            // ===================================================================
            foreach ($todosIds as $equipoId) {
                $cantidadActual = $countActuales[$equipoId] ?? 0;
                $cantidadNueva = $countNuevos[$equipoId] ?? 0;
                $diferencia = $cantidadNueva - $cantidadActual;

                if ($diferencia > 0) {
                    // ============================================================
                    // AGREGAR UNIDADES (diferencia positiva)
                    // ============================================================
                    $equipo = Equipo::findOrFail($equipoId);

                    for ($i = 0; $i < $diferencia; $i++) {
                        // Decrementar stock
                        if ($equipo->es_individual) {
                            $equipo->estado = 'prestado';
                            $equipo->cantidad_disponible = 0;
                        } else {
                            if ($equipo->cantidad_disponible <= 0) {
                                throw new \Exception("No hay stock disponible de {$equipo->nombre_equipo}");
                            }
                            $equipo->cantidad_disponible -= 1;
                            if ($equipo->cantidad_disponible == 0) {
                                $equipo->estado = 'prestado';
                            }
                        }
                        $equipo->save();

                        // Crear registro en prestamo_equipos
                        PrestamoEquipo::create([
                            'prestamo_id' => $prestamo->id,
                            'equipo_id' => $equipoId,
                            'estado' => 'activo',
                        ]);
                    }

                } elseif ($diferencia < 0) {
                    // ============================================================
                    // QUITAR UNIDADES (diferencia negativa)
                    // ============================================================
                    $cantidadAQuitar = abs($diferencia);
                    $equipo = Equipo::findOrFail($equipoId);

                    // Obtener registros activos de este equipo en el préstamo
                    $registrosActivos = PrestamoEquipo::where('prestamo_id', $prestamo->id)
                        ->where('equipo_id', $equipoId)
                        ->where('estado', 'activo')
                        ->limit($cantidadAQuitar)
                        ->get();

                    foreach ($registrosActivos as $registro) {
                        // Marcar como cancelado
                        $registro->update(['estado' => 'cancelado']);

                        // Incrementar stock
                        if ($equipo->es_individual) {
                            $equipo->estado = 'disponible';
                            $equipo->cantidad_disponible = 1;
                        } else {
                            $equipo->cantidad_disponible += 1;
                            if ($equipo->cantidad_disponible > 0) {
                                $equipo->estado = 'disponible';
                            }
                        }
                        $equipo->save();
                    }
                }
                // Si diferencia == 0, no hacer nada (cantidad no cambió)
            }

            // Actualizar datos del préstamo
            $prestamo->update([
                'fecha_devolucion_esperada' => $datos['fecha_devolucion_esperada'],
                'observaciones_prestamo' => $datos['observaciones'] ?? $prestamo->observaciones_prestamo,
            ]);

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