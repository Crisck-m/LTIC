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
        'tipo_personalizado',
        'es_individual',
        'cantidad_total',
        'cantidad_disponible',
        'marca',
        'modelo',
        'caracteristicas',
        'estado',
        'user_id',
    ];

    protected $casts = [
        'es_individual' => 'boolean',
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