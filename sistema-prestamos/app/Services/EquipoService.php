<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\Prestamo;

/**
 * EquipoService
 *
 * Servicio que encapsula la lógica de negocio para la gestión del inventario de equipos.
 * Proporciona operaciones CRUD y lógica especial como la eliminación inteligente
 * (dar de baja vs eliminar físicamente según si tiene préstamos asociados).
 */
class EquipoService
{
    /**
     * Lista los equipos del inventario con filtros opcionales y paginación.
     *
     * Aplica filtros de búsqueda por texto (marca, modelo, nombre, tipo),
     * filtro por tipo de equipo y filtro por estado.
     * Resultados ordenados del más reciente al más antiguo, de 10 en 10.
     *
     * @param  string|null $search Texto para buscar en marca, modelo, nombre_equipo o tipo.
     * @param  string|null $tipo   Filtra por tipo de equipo exacto.
     * @param  string|null $estado Filtra por estado (disponible, prestado, mantenimiento, etc.).
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listarEquipos($search, $tipo, $estado)
    {
        $query = Equipo::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('marca', 'LIKE', "%{$search}%")
                    ->orWhere('modelo', 'LIKE', "%{$search}%")
                    ->orWhere('nombre_equipo', 'LIKE', "%{$search}%")
                    ->orWhere('tipo', 'LIKE', "%{$search}%");
            });
        }

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    /**
     * Crea un nuevo equipo en el inventario.
     *
     * @param  array<string, mixed> $datos Datos validados del formulario.
     * @return Equipo El equipo recién creado.
     */
    public function crearEquipo(array $datos)
    {
        return Equipo::create($datos);
    }

    /**
     * Actualiza los datos de un equipo existente.
     *
     * @param  Equipo               $equipo El equipo a actualizar.
     * @param  array<string, mixed> $datos  Datos validados del formulario.
     * @return bool `true` si la actualización fue exitosa.
     */
    public function actualizarEquipo(Equipo $equipo, array $datos)
    {
        return $equipo->update($datos);
    }

    /**
     * Marca un equipo como dado de baja (desactivado) sin eliminarlo.
     *
     * Solo aplica si el equipo no está actualmente en estado 'prestado'.
     * Si está prestado, retorna `false` sin realizar cambios.
     *
     * @param  Equipo $equipo El equipo a dar de baja.
     * @return bool `true` si se dio de baja correctamente, `false` si está prestado.
     */
    public function darDeBajaEquipo(Equipo $equipo)
    {
        if ($equipo->estado === 'prestado') {
            return false;
        }

        return $equipo->update(['estado' => 'baja']);
    }

    /**
     * Elimina un equipo del sistema o lo da de baja según si tiene préstamos asociados.
     *
     * **Lógica de decisión:**
     * - Si el equipo tiene **préstamos registrados** (historiales): se cambia su estado
     *   a 'dado_de_baja' y se pone la cantidad disponible en 0 (si aplica).
     *   El registro se conserva para mantener la integridad del historial.
     * - Si el equipo **NO tiene préstamos**: se elimina físicamente de la base de datos.
     *
     * @param  Equipo $equipo El equipo a procesar.
     * @return string|false Retorna 'dado_de_baja', 'eliminado', o `false` si ocurrió un error.
     */
    public function eliminarEquipo(Equipo $equipo)
    {
        // Verificar si tiene préstamos asociados
        $tienePrestamos = $equipo->prestamos()->exists();

        if ($tienePrestamos) {
            // ===================================================================
            // CASO: TIENE PRÉSTAMOS → DAR DE BAJA (no eliminar)
            // ===================================================================
            $equipo->estado = 'dado_de_baja';

            // Si es equipo por cantidad, poner cantidad disponible en 0
            if (!$equipo->es_individual) {
                $equipo->cantidad_disponible = 0;
            }

            $equipo->save();

            return 'dado_de_baja';
        } else {
            // ===================================================================
            // CASO: NO TIENE PRÉSTAMOS → ELIMINAR FÍSICAMENTE
            // ===================================================================
            try {
                $equipo->delete();
                return 'eliminado';
            } catch (\Exception $e) {
                \Log::error('Error al eliminar equipo: ' . $e->getMessage());
                return false;
            }
        }
    }
}