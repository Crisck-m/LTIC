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

            // Registrar equipos en la tabla intermedia con sus cantidades
            foreach ($datos['equipo_id'] as $index => $equipoId) {
                $equipo = Equipo::findOrFail($equipoId);
                $cantidad = $datos['cantidades'][$index] ?? 1;  // NUEVO: Obtener cantidad

                // ===================================================================
                // DECREMENTAR CANTIDAD DISPONIBLE
                // ===================================================================
                if ($equipo->es_individual) {
                    // LAPTOP: Cambiar estado a 'prestado'
                    $equipo->estado = 'prestado';
                    $equipo->cantidad_disponible = 0;
                    $cantidad = 1;  // Laptops siempre son 1 unidad
                } else {
                    // EQUIPOS POR CANTIDAD: Decrementar por N unidades
                    if ($equipo->cantidad_disponible < $cantidad) {
                        throw new \Exception("No hay stock suficiente de {$equipo->nombre_equipo}");
                    }
                    $equipo->cantidad_disponible -= $cantidad;  // MODIFICADO: Restar N unidades

                    // Si se agotó el stock, cambiar estado
                    if ($equipo->cantidad_disponible == 0) {
                        $equipo->estado = 'prestado';
                    }
                }
                $equipo->save();

                // Crear registro en prestamo_equipos CON CANTIDAD
                PrestamoEquipo::create([
                    'prestamo_id' => $prestamo->id,
                    'equipo_id' => $equipoId,
                    'cantidad' => $cantidad,  // NUEVO: Almacenar cantidad prestada
                    'estado' => 'activo',
                ]);
            }

            return $prestamo;
        });
    }

    /**
     * Registrar devolución de equipo(s)
     */
    public function registrarDevolucion(Prestamo $prestamo, array $prestamoEquipoIds, array $datos)
    {
        return DB::transaction(function () use ($prestamo, $prestamoEquipoIds, $datos) {
            foreach ($prestamoEquipoIds as $prestamoEquipoId) {
                // Buscar el registro específico en prestamo_equipos por su ID
                $prestamoEquipo = PrestamoEquipo::where('id', $prestamoEquipoId)
                    ->where('prestamo_id', $prestamo->id)
                    ->where('estado', 'activo')
                    ->firstOrFail();

                // Obtener la cantidad que se está devolviendo
                $cantidadDevuelta = $prestamoEquipo->cantidad ?? 1;

                // Actualizar el registro de devolución
                $prestamoEquipo->update([
                    'fecha_devolucion_real' => now(),
                    'practicante_recibe_id' => $datos['practicante_recibe_id'],
                    'observaciones_devolucion' => $datos['observaciones_devolucion'] ?? null,
                    'estado' => 'devuelto',
                ]);

                // ===================================================================
                // INCREMENTAR CANTIDAD DISPONIBLE CORRECTAMENTE
                // ===================================================================
                $equipo = Equipo::findOrFail($prestamoEquipo->equipo_id);

                if ($equipo->es_individual) {
                    // LAPTOP: Cambiar estado a 'disponible'
                    $equipo->estado = 'disponible';
                    $equipo->cantidad_disponible = 1;
                } else {
                    // OTROS: Incrementar por la cantidad que fue prestada
                    $equipo->cantidad_disponible += $cantidadDevuelta;

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
            // ===================================================================
            // OBTENER EQUIPOS ACTUALES DEL PRÉSTAMO
            // ===================================================================
            $equiposActuales = $prestamo->prestamoEquipos()
                ->where('estado', 'activo')
                ->pluck('equipo_id')
                ->toArray();

            // ===================================================================
            // IDENTIFICAR SOLO LOS EQUIPOS NUEVOS
            // ===================================================================
            $equiposEnviados = $datos['equipo_id'];  // Todos los equipos recibidos
            $equiposNuevos = array_diff($equiposEnviados, $equiposActuales);  // Solo los nuevos

            // Crear un mapa que asocie cada nuevo equipo con su índice en el array original
            // para obtener la cantidad correcta
            $indicesNuevos = [];
            foreach ($equiposNuevos as $nuevoId) {
                // Encontrar la posición de este equipo en el array original
                $indice = array_search($nuevoId, $equiposEnviados);
                if ($indice !== false) {
                    $indicesNuevos[$nuevoId] = $indice;
                }
            }

            // ===================================================================
            // AGREGAR SOLO LOS EQUIPOS NUEVOS
            // ===================================================================
            foreach ($indicesNuevos as $equipoId => $indiceOriginal) {
                $equipo = Equipo::findOrFail($equipoId);
                $cantidad = $datos['cantidades'][$indiceOriginal] ?? 1;

                // Validar disponibilidad
                if (!$equipo->es_individual && $cantidad > $equipo->cantidad_disponible) {
                    throw new \Exception("No hay stock suficiente de {$equipo->nombre_equipo}");
                }

                // Decrementar stock
                if ($equipo->es_individual) {
                    $equipo->estado = 'prestado';
                    $equipo->cantidad_disponible = 0;
                    $cantidad = 1;
                } else {
                    $equipo->cantidad_disponible -= $cantidad;
                    if ($equipo->cantidad_disponible == 0) {
                        $equipo->estado = 'prestado';
                    }
                }
                $equipo->save();

                // Crear registro con cantidad
                PrestamoEquipo::create([
                    'prestamo_id' => $prestamo->id,
                    'equipo_id' => $equipoId,
                    'cantidad' => $cantidad,  // Usar campo cantidad
                    'estado' => 'activo',
                ]);
            }

            // ===================================================================
            // MANEJAR EQUIPOS QUE SE REMOVIERON (si aplica)
            // ===================================================================
            $equiposRemovidos = array_diff($equiposActuales, $equiposEnviados);
            foreach ($equiposRemovidos as $equipoRemovido) {
                // Obtener el registro activo de este equipo
                $prestamoEquipo = PrestamoEquipo::where('prestamo_id', $prestamo->id)
                    ->where('equipo_id', $equipoRemovido)
                    ->where('estado', 'activo')
                    ->first();

                if ($prestamoEquipo) {
                    // Marcar como cancelado
                    $prestamoEquipo->update(['estado' => 'cancelado']);

                    // Incrementar stock del equipo
                    $equipo = Equipo::findOrFail($equipoRemovido);
                    $cantidadDevuelta = $prestamoEquipo->cantidad ?? 1;

                    if ($equipo->es_individual) {
                        $equipo->estado = 'disponible';
                        $equipo->cantidad_disponible = 1;
                    } else {
                        $equipo->cantidad_disponible += $cantidadDevuelta;
                        if ($equipo->cantidad_disponible > 0) {
                            $equipo->estado = 'disponible';
                        }
                    }
                    $equipo->save();
                }
            }

            // Actualizar datos del préstamo si es necesario
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