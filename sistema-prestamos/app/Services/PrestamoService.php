<?php

namespace App\Services;

use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PrestamoService
{
    public function listarPrestamos($search, $estado)
    {
        $query = Prestamo::with(['equipo', 'estudiante', 'practicante']);

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

    public function obtenerPendientes()
    {
        return Prestamo::with(['equipo', 'estudiante'])
                ->where('estado', 'activo')
                ->orderBy('fecha_prestamo', 'asc')
                ->paginate(10);
    }

    public function registrarSalida(array $datos)
    {
        return DB::transaction(function () use ($datos) {
            $prestamo = Prestamo::create([
                'equipo_id'     => $datos['equipo_id'],
                'estudiante_id' => $datos['estudiante_id'],
                
                // CAMBIO: Guardado directo y nativo
                'practicante_id'=> $datos['practicante_id'], 
                
                'user_id'       => Auth::id(),
                'fecha_prestamo'=> now(),
                'estado'        => 'activo',
                'observaciones_prestamo' => $datos['observaciones'] ?? null
            ]);

            $prestamo->equipo->update(['estado' => 'prestado']);

            return $prestamo;
        });
    }

    public function registrarDevolucion(Prestamo $prestamo, $observaciones)
    {
        return DB::transaction(function () use ($prestamo, $observaciones) {
            $prestamo->update([
                'fecha_devolucion_real' => now(),
                'estado' => 'finalizado',
                'observaciones_devolucion' => $observaciones
            ]);

            $prestamo->equipo->update(['estado' => 'disponible']);

            return $prestamo;
        });
    }
}