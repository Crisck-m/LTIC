<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $fillable = [
        'cedula',        // ✅ CAMBIADO de 'matricula' a 'cedula'
        'nombre',
        'apellido',
        'email',
        'telefono',
        'carrera',
        'tipo',
        'activo',
        'observaciones'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relación: Préstamos donde este estudiante RECIBIÓ el equipo
    public function prestamosRecibidos()
    {
        return $this->hasMany(Prestamo::class, 'estudiante_id');
    }

    // Relación: Préstamos donde este estudiante REGISTRÓ como practicante
    public function prestamosRegistrados()
    {
        return $this->hasMany(Prestamo::class, 'practicante_id');
    }

    // Relación: Devoluciones donde este estudiante RECIBIÓ el equipo como practicante
    public function devolucionesRecibidas()
    {
        return $this->hasMany(Prestamo::class, 'practicante_recibe_id');
    }

    // Scope para estudiantes activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope para búsqueda (ACTUALIZADO)
    public function scopeBuscar($query, $search)
    {
        return $query->where('cedula', 'LIKE', "%{$search}%")      // ✅ CAMBIADO
            ->orWhere('nombre', 'LIKE', "%{$search}%")
            ->orWhere('apellido', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%");
    }

    // Accessor para nombre completo
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }
}