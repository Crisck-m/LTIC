<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Carrera
 *
 * Representa una carrera universitaria o programa académico.
 * Se utiliza para asociar estudiantes a su carrera de forma normalizada.
 *
 * @property int    $id     Identificador único de la carrera.
 * @property string $nombre Nombre de la carrera (ej: "Ingeniería en Sistemas").
 */
class Carrera extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = ['nombre'];

    /**
     * Relación: Una carrera tiene muchos estudiantes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class);
    }
}