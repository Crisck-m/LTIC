<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    // CAMBIO TOTAL AQUÍ: Ahora la relación se llama 'practicante'
    // y apunta a la columna 'practicante_id'
    public function practicante()
    {
        return $this->belongsTo(Estudiante::class, 'practicante_id');
    }
}