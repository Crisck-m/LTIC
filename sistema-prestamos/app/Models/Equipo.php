<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_equipo',
        'tipo',
        'marca',
        'modelo',
        'estado',
        'caracteristicas'
    ];

    // Relación: Préstamos de este equipo
    public function prestamos()
    {
        return $this->hasMany(Prestamo::class);
    }

    // Scope: Solo equipos disponibles
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible');
    }
}