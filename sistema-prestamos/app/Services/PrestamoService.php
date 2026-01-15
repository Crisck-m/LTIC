<?php

namespace App\Services;

use App\Models\Prestamo;
use App\Models\Historial;
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
                'practicante_id'=> $datos['practicante_id'], 
                'user_id'       => Auth::id(),
                'fecha_prestamo'=> now(),
                'fecha_devolucion_esperada' => $datos['fecha_devolucion_esperada'],
                'estado'        => 'activo',
                'observaciones_prestamo' => $datos['observaciones'] ?? null,
                'notificar_retorno' => $datos['notificar_retorno'] ?? false,
                'periodo_notificacion' => $datos['periodo_notificacion'] ?? null
            ]);

            $prestamo->equipo->update(['estado' => 'prestado']);

            $practicante = \App\Models\Estudiante::find($datos['practicante_id']);
            $estudiante = \App\Models\Estudiante::find($datos['estudiante_id']);
            $equipo = \App\Models\Equipo::find($datos['equipo_id']);

            Historial::registrar(
                'Préstamo Realizado',
                "El practicante {$practicante->nombre} {$practicante->apellido} entregó el equipo {$equipo->tipo} ({$equipo->codigo_puce}) al estudiante {$estudiante->nombre} {$estudiante->apellido}."
            );

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

            Historial::registrar(
                'Devolución Completada',
                "Se recibió el equipo {$prestamo->equipo->codigo_puce} (Devuelto por: {$prestamo->estudiante->nombre} {$prestamo->estudiante->apellido}). Observación: " . ($observaciones ?? 'Ninguna')
            );

            return $prestamo;
        });
    }
}