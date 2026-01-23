<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\Prestamo;

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
                  ->orWhere('tipo', 'LIKE', "%{$search}%");
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

    public function darDeBajaEquipo(Equipo $equipo)
    {
        if ($equipo->estado === 'prestado') {
            return false;
        }
        
        return $equipo->update(['estado' => 'baja']);
    }
}