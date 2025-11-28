<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricula',
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

    // Relación con préstamos como responsable
    public function prestamosResponsable()
    {
        return $this->hasMany(Prestamo::class, 'estudiante_responsable_id');
    }

    // Relación con préstamos como receptor
    public function prestamosReceptor()
    {
        return $this->hasMany(Prestamo::class, 'estudiante_receptor_id');
    }

    // Scope para estudiantes activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope para búsqueda
    public function scopeBuscar($query, $search)
    {
        return $query->where('matricula', 'LIKE', "%{$search}%")
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