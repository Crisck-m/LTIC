<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'fecha_prestamo' => 'datetime',
        'fecha_devolucion_esperada' => 'date',
        'fecha_devolucion_real' => 'datetime',
        'recordatorio_enviado' => 'boolean',
    ];

    // Relación: Equipo prestado
    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    // Relación: Estudiante que RECIBE el equipo
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    // Relación: Estudiante que REGISTRA el préstamo (pasante/practicante)
    // La columna en BD es 'practicante_id'
    public function practicante()
    {
        return $this->belongsTo(Estudiante::class, 'practicante_id');
    }

    // Relación: Estudiante (practicante) que REVISA/RECIBE el equipo en la devolución
    // La columna en BD es 'practicante_recibe_id'
    public function practicanteDevolucion()
    {
        return $this->belongsTo(Estudiante::class, 'practicante_recibe_id');
    }
}