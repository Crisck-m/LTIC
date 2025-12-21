<?php

namespace App\Services;

use App\Models\Equipo;
use Illuminate\Support\Facades\DB;

class EquipoService
{
    public function listarEquipos($search, $estado, $tipo)
    {
        $query = Equipo::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('codigo_puce', 'LIKE', "%{$search}%")
                  ->orWhere('marca', 'LIKE', "%{$search}%")
                  ->orWhere('modelo', 'LIKE', "%{$search}%");
            });
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        return $query->latest()->paginate(10);
    }

    public function crearEquipo(array $datos)
    {
        return Equipo::create($datos);
    }

    public function actualizarEquipo(Equipo $equipo, array $datos)
    {
        $equipo->update($datos);
        return $equipo;
    }

    public function eliminarEquipo(Equipo $equipo)
    {
        return $equipo->delete();
    }
}