<?php

namespace App\Services;

use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PrestamoService
{
    public function listarPrestamos($search, $estado, $fechaDesde = null, $fechaHasta = null)
    {
        $query = Prestamo::with(['equipo', 'estudiante', 'practicante']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('estudiante', function ($subQ) use ($search) {
                    $subQ->where('nombre', 'LIKE', "%{$search}%")
                        ->orWhere('apellido', 'LIKE', "%{$search}%");
                })->orWhereHas('equipo', function ($subQ) use ($search) {
                    $subQ->where('codigo_puce', 'LIKE', "%{$search}%")
                        ->orWhere('tipo', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        // Filtro por rango de fechas
        if ($fechaDesde) {
            $query->whereDate('fecha_prestamo', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->whereDate('fecha_prestamo', '<=', $fechaHasta);
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
                'equipo_id' => $datos['equipo_id'],
                'estudiante_id' => $datos['estudiante_id'],
                'practicante_id' => $datos['practicante_id'],
                'user_id' => Auth::id(),
                'fecha_prestamo' => now(),
                'fecha_devolucion_esperada' => $datos['fecha_devolucion_esperada'],
                'estado' => 'activo',
                'observaciones_prestamo' => $datos['observaciones'] ?? null,
                'notificar_retorno' => $datos['notificar_retorno'] ?? false,
                'periodo_notificacion' => $datos['periodo_notificacion'] ?? null
            ]);

            $prestamo->equipo->update(['estado' => 'prestado']);

            return $prestamo;
        });
    }

    public function registrarDevolucion(Prestamo $prestamo, $observaciones, $pasanteDevolucionId)
    {
        return DB::transaction(function () use ($prestamo, $observaciones, $pasanteDevolucionId) {
            $prestamo->update([
                'fecha_devolucion_real' => now(),
                'estado' => 'finalizado',
                'observaciones_devolucion' => $observaciones,
                'practicante_recibe_id' => $pasanteDevolucionId
            ]);

            $prestamo->equipo->update(['estado' => 'disponible']);

            return $prestamo;
        });
    }
}