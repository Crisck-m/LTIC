<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $guarded = []; // Permite guardar todo

    // Relación: Un préstamo es de un equipo
    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    // Relación: Un préstamo es para un estudiante
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class); 
    }

    // Relación: Un préstamo lo hizo un usuario (admin/pasante)
    public function responsable()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}