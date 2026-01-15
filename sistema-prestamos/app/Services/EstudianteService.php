<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Prestamo; // <-- AGREGAR ESTO

class EstudianteService
{
    public function listarEstudiantes($search, $tipo)
    {
        $query = Estudiante::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('matricula', 'like', "%{$search}%");
            });
        }

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    public function crearEstudiante(array $datos)
    {
        return Estudiante::create($datos);
    }

    public function actualizarEstudiante(Estudiante $estudiante, array $datos)
    {
        return $estudiante->update($datos);
    }

    // --- AQUÍ ESTÁ LA CORRECCIÓN ---
    public function eliminarEstudiante(Estudiante $estudiante)
    {
        // Verificar si el estudiante aparece en algún préstamo (como estudiante O como practicante)
        $tieneHistorial = Prestamo::where('estudiante_id', $estudiante->id)
                            ->orWhere('practicante_id', $estudiante->id)
                            ->exists();

        // Si tiene historial, NO borramos y devolvemos false
        if ($tieneHistorial) {
            return false;
        }

        // Si está limpio, lo borramos y devolvemos true
        return $estudiante->delete();
    }
}