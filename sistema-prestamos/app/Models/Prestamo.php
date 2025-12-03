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

    // Relación: Pasante que ATENDIÓ (Registró)
    public function pasante()
    {
        return $this->belongsTo(Estudiante::class, 'pasante_id');
    }
}