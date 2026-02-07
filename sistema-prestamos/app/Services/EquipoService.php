<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\Prestamo;

class EquipoService
{
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

    public function crearEquipo(array $datos)
    {
        return Equipo::create($datos);
    }

    public function actualizarEquipo(Equipo $equipo, array $datos)
    {
        return $equipo->update($datos);
    }

    public function darDeBajaEquipo(Equipo $equipo)
    {
        if ($equipo->estado === 'prestado') {
            return false;
        }

        return $equipo->update(['estado' => 'baja']);
    }

    /**
     * Eliminar equipo o darlo de baja según si tiene préstamos
     * Retorna: 'eliminado', 'dado_de_baja', o false si hay error
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