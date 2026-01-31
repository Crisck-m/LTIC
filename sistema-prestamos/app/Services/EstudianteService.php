<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Prestamo;

class EstudianteService
{
    public function listarEstudiantes($search, $tipo)
    {
        $query = Estudiante::query();

        if ($search) {
            $query->buscar($search);  // Usa el scope definido en el modelo
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

    public function eliminarEstudiante(Estudiante $estudiante)
    {
        // Verificar si el estudiante aparece en algún préstamo
        $tienePrestamos = Prestamo::where('estudiante_id', $estudiante->id)
            ->orWhere('practicante_id', $estudiante->id)
            ->exists();

        if ($tienePrestamos) {
            return false;
        }

        return $estudiante->delete();
    }
}