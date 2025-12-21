<?php

namespace App\Services;

use App\Models\Prestamo;
use App\Models\Equipo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PrestamoService
{
    // 1. Listar con filtros (Buscador + Estado)
    public function listarPrestamos($search, $estado)
    {
        $query = Prestamo::with(['equipo', 'estudiante', 'pasante']);

        if ($search) {
            $query->whereHas('estudiante', function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('apellido', 'LIKE', "%{$search}%");
            })->orWhereHas('equipo', function($q) use ($search) {
                $q->where('codigo_puce', 'LIKE', "%{$search}%")
                  ->orWhere('tipo', 'LIKE', "%{$search}%");
            });
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        return $query->latest()->paginate(10);
    }

    // 2. Obtener solo los pendientes (Para el módulo de Devoluciones)
    public function obtenerPendientes()
    {
        return Prestamo::with(['equipo', 'estudiante'])
                ->where('estado', 'activo')
                ->orderBy('fecha_prestamo', 'asc')
                ->paginate(10);
    }

    // 3. REGISTRAR SALIDA (Transacción Crítica)
    public function registrarSalida(array $datos)
    {
        return DB::transaction(function () use ($datos) {
            // A. Crear el préstamo
            $prestamo = Prestamo::create([
                'equipo_id'     => $datos['equipo_id'],
                'estudiante_id' => $datos['estudiante_id'],
                'pasante_id'    => $datos['pasante_id'], // El pasante que seleccionaste
                'user_id'       => Auth::id(),           // El usuario del sistema (auditoría)
                'fecha_prestamo'=> now(),
                'estado'        => 'activo',
                'observaciones_prestamo' => $datos['observaciones'] ?? null
            ]);

            // B. Cambiar estado del equipo a "Prestado"
            $prestamo->equipo->update(['estado' => 'prestado']);

            return $prestamo;
        });
    }

    // 4. REGISTRAR DEVOLUCIÓN (Transacción Crítica)
    public function registrarDevolucion(Prestamo $prestamo, $observaciones)
    {
        return DB::transaction(function () use ($prestamo, $observaciones) {
            // A. Actualizar el préstamo
            $prestamo->update([
                'fecha_devolucion_real' => now(),
                'estado' => 'finalizado',
                'observaciones_devolucion' => $observaciones
            ]);

            // B. Liberar el equipo (Disponible)
            $prestamo->equipo->update(['estado' => 'disponible']);

            return $prestamo;
        });
    }
}