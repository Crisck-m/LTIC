<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestamoEquipo extends Model
{
    use HasFactory;

    protected $table = 'prestamo_equipos';

    protected $fillable = [
        'prestamo_id',
        'equipo_id',
        'fecha_devolucion_real',
        'practicante_recibe_id',
        'observaciones_devolucion',
        'estado',
    ];

    protected $casts = [
        'fecha_devolucion_real' => 'datetime',
    ];

    // Relación: ¿A qué préstamo pertenece este equipo?
    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }

    // Relación: ¿Qué equipo es?
    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    // Relación: ¿Quién recibió este equipo en la devolución?
    public function practicanteRecibe()
    {
        return $this->belongsTo(Estudiante::class, 'practicante_recibe_id');
    }
}