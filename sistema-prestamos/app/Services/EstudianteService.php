<?php

namespace App\Services;

use App\Models\Estudiante;
use Illuminate\Support\Facades\DB;

class EstudianteService
{
    // Lógica para listar con filtros
    public function listarEstudiantes($search, $tipo)
    {
        $query = Estudiante::query();

        // Filtro de texto
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('apellido', 'LIKE', "%{$search}%")
                  ->orWhere('matricula', 'LIKE', "%{$search}%");
            });
        }

        // Filtro de rol
        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        return $query->latest()->paginate(10);
    }

    // Lógica para crear
    public function crearEstudiante(array $datos)
    {
        return DB::transaction(function () use ($datos) {
            return Estudiante::create($datos);
        });
    }

    // Lógica para actualizar
    public function actualizarEstudiante(Estudiante $estudiante, array $datos)
    {
        return DB::transaction(function () use ($estudiante, $datos) {
            $estudiante->update($datos);
            return $estudiante;
        });
    }

    // Lógica para eliminar
    public function eliminarEstudiante(Estudiante $estudiante)
    {
        return $estudiante->delete();
    }
}