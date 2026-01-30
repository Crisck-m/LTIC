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
        'fecha_devolucion_esperada' => 'datetime',
        'fecha_devolucion_real' => 'datetime',
        'notificar_retorno' => 'boolean',
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
    // La columna en BD es 'pasante_id'
    public function practicante()
    {
        return $this->belongsTo(Estudiante::class, 'pasante_id');
    }

    // Relación: Estudiante (practicante) que REVISA/RECIBE el equipo en la devolución
    public function practicanteDevolucion()
    {
        return $this->belongsTo(Estudiante::class, 'pasante_devolucion_id');
    }
}