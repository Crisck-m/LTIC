<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\Prestamo; // Importante

class EquipoService
{
    public function listarEquipos($search, $estado)
    {
        $query = Equipo::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('marca', 'LIKE', "%{$search}%")
                  ->orWhere('modelo', 'LIKE', "%{$search}%")
                  ->orWhere('codigo_puce', 'LIKE', "%{$search}%")
                  ->orWhere('serie', 'LIKE', "%{$search}%");
            });
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

    // --- LÓGICA DE ELIMINACIÓN SEGURA ---
    public function eliminarEquipo(Equipo $equipo)
    {
        // Si el equipo está en algún préstamo (activo o histórico), NO se borra
        $tieneHistorial = Prestamo::where('equipo_id', $equipo->id)->exists();

        if ($tieneHistorial) {
            return false;
        }

        return $equipo->delete();
    }
}