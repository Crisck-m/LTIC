<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $fillable = [
        'estudiante_id',
        'practicante_id',
        'user_id',
        'fecha_prestamo',
        'fecha_devolucion_esperada',
        'estado',
        'observaciones_prestamo',
    ];

    protected $casts = [
        'fecha_prestamo' => 'datetime',
        'fecha_devolucion_esperada' => 'date',
    ];

    // Relación: Estudiante que RECIBE el préstamo
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    // Relación: Practicante que REGISTRA el préstamo
    public function practicante()
    {
        return $this->belongsTo(Estudiante::class, 'practicante_id');
    }

    // NUEVA RELACIÓN: Equipos asociados a este préstamo
    public function prestamoEquipos()
    {
        return $this->hasMany(PrestamoEquipo::class);
    }

    // NUEVA RELACIÓN: Obtener directamente los equipos (sin la tabla intermedia)
    public function equipos()
    {
        return $this->hasManyThrough(
            Equipo::class,           // Modelo final
            PrestamoEquipo::class,   // Tabla intermedia
            'prestamo_id',           // FK en prestamo_equipos
            'id',                    // FK en equipos
            'id',                    // PK en prestamos
            'equipo_id'              // FK en prestamo_equipos
        );
    }

    // RELACIÓN LEGACY: Mantener compatibilidad con código antiguo
    // (la eliminaremos después de migrar todo)
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    // Método auxiliar: ¿Todos los equipos fueron devueltos?
    public function todosEquiposDevueltos()
    {
        return $this->prestamoEquipos()
            ->where('estado', '!=', 'devuelto')
            ->count() === 0;
    }

    // Método auxiliar: Obtener equipos activos (no devueltos)
    public function equiposActivos()
    {
        return $this->prestamoEquipos()
            ->where('estado', 'activo')
            ->with('equipo')
            ->get();
    }

    // Método auxiliar: Obtener equipos devueltos
    public function equiposDevueltos()
    {
        return $this->prestamoEquipos()
            ->where('estado', 'devuelto')
            ->with('equipo')
            ->get();
    }
}