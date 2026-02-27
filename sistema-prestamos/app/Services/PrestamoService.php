<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\Prestamo;
use App\Models\PrestamoEquipo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * PrestamoService
 *
 * Servicio central que encapsula toda la lógica de negocio relacionada con préstamos.
 * Maneja el ciclo de vida completo:
 * - Listado con filtros.
 * - Registro de salida de equipos (creación de préstamos).
 * - Registro de devoluciones (parciales o totales).
 * - Actualización de préstamos existentes (agregar/quitar equipos).
 * - Consulta de préstamos pendientes.
 *
 * Todas las operaciones que modifican datos de equipos y préstamos se ejecutan
 * dentro de **transacciones de base de datos** para garantizar la consistencia.
 */
class PrestamoService
{
    /**
     * Listar préstamos con filtros opcionales y paginación.
     *
     * Soporta búsqueda por texto (nombre/apellido del estudiante, nombre/tipo del equipo),
     * filtrado por estado (incluyendo el estado virtual 'atrasado') y por rango de fechas.
     *
     * El estado `atrasado` no existe en la base de datos; es un filtrado virtual que
     * selecciona préstamos activos cuya fecha de devolución ya pasó.
     *
     * @param  string|null $search     Búsqueda por nombre/apellido del estudiante o nombre/tipo del equipo.
     * @param  string|null $estado     Estado del préstamo: 'activo', 'finalizado' o 'atrasado'.
     * @param  string|null $fechaDesde Fecha de inicio del rango (formato Y-m-d).
     * @param  string|null $fechaHasta Fecha de fin del rango (formato Y-m-d).
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Préstamos paginados de 15 en 15.
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
     * Registrar salida de equipo(s) - Crear un nuevo préstamo.
     *
     * Crea el registro principal del préstamo y, por cada equipo en la lista,
     * registra la asociación en `prestamo_equipos` y decrementa el stock disponible.
     *
     * **Lógica de stock por tipo de equipo:**
     * - **Laptop (individual)**: Cambia estado a 'prestado', `cantidad_disponible = 0`.
     * - **Otros (por cantidad)**: Resta la cantidad solicitada de `cantidad_disponible`.
     *   Si se agota el stock, cambia estado a 'prestado'.
     *
     * Toda la operación se ejecuta dentro de una transacción de base de datos.
     *
     * @param  array<string, mixed> $datos Array con: estudiante_id, practicante_id,
     *                                     fecha_devolucion_esperada, equipo_id (array),
     *                                     cantidades (array), observaciones.
     * @return Prestamo El préstamo recién creado.
     * @throws \Exception Si no hay stock suficiente para algún equipo.
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
     * Registrar devolución de uno o más equipos de un préstamo activo.
     *
     * Procesa cada ID de `prestamo_equipos` recibido:
     * 1. Marca el registro como 'devuelto' con fecha, practicante y observaciones.
     * 2. Incrementa el stock disponible del equipo en el inventario.
     * 3. Si todos los equipos del préstamo fueron devueltos, cierra el préstamo ('finalizado').
     *
     * **Lógica de stock:**
     * - **Laptop**: Cambia estado a 'disponible', `cantidad_disponible = 1`.
     * - **Otros**: Suma la cantidad original prestada a `cantidad_disponible`.
     *
     * Toda la operación se ejecuta dentro de una transacción de base de datos.
     *
     * @param  Prestamo      $prestamo          El préstamo en proceso de devolución.
     * @param  array<int>    $prestamoEquipoIds IDs de los registros en `prestamo_equipos` a devolver.
     * @param  array<string, mixed> $datos      Array con: practicante_recibe_id, observaciones_devolucion.
     * @return Prestamo El préstamo actualizado.
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
     * Actualizar préstamo: agregar equipos nuevos y/o remover equipos existentes.
     *
     * Compara la lista de equipos enviada contra los equipos actualmente activos
     * en el préstamo para identificar:
     * - **Equipos nuevos** (en enviados pero no en activos): se agregan y se descuenta su stock.
     * - **Equipos removidos** (en activos pero no en enviados): se marcan como 'cancelado'
     *   y se devuelve su stock al inventario.
     * - **Equipos existentes** (en ambas listas): no se tocan.
     *
     * Al final, actualiza la fecha de devolución esperada y las observaciones del préstamo.
     * Toda la operación se ejecuta dentro de una transacción de base de datos.
     *
     * @param  Prestamo             $prestamo El préstamo a actualizar.
     * @param  array<string, mixed> $datos    Array con: equipo_id (array combinado de IDs),
     *                                        cantidades (array), fecha_devolucion_esperada, observaciones.
     * @return Prestamo El préstamo actualizado.
     * @throws \Exception Si no hay stock suficiente para algún equipo nuevo.
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
     * Obtener préstamos pendientes de devolución con paginación.
     *
     * Retorna todos los préstamos en estado 'activo', es decir, aquellos
     * que tienen equipos que aún no han sido devueltos.
     * Ordenados del más reciente al más antiguo, de 15 en 15.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function obtenerPendientes()
    {
        return Prestamo::with(['estudiante', 'practicante', 'prestamoEquipos.equipo'])
            ->where('estado', 'activo')
            ->latest('fecha_prestamo')
            ->paginate(15);
    }
}