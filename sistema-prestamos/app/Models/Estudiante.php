<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Estudiante
 *
 * Representa a un usuario del sistema que puede recibir préstamos de equipos.
 * Puede ser de dos tipos:
 * - **estudiante**: persona que solicita el préstamo.
 * - **practicante**: persona encargada de registrar y entregar los préstamos.
 *
 * @property int         $id            Identificador único.
 * @property string      $cedula        Número de cédula (10 dígitos, único).
 * @property string      $nombre        Nombre del estudiante.
 * @property string      $apellido      Apellido del estudiante.
 * @property string      $email         Correo electrónico (único).
 * @property string|null $telefono      Número de teléfono (10 dígitos, opcional).
 * @property string|null $carrera       Nombre de la carrera (texto plano, compatibilidad con datos históricos).
 * @property int|null    $carrera_id    FK a la tabla `carreras` (campo nuevo y normalizado).
 * @property string      $tipo          Tipo de usuario: 'estudiante' o 'practicante'.
 * @property bool        $activo        Indica si el estudiante está activo en el sistema.
 * @property string|null $observaciones Notas adicionales sobre el estudiante.
 */
class Estudiante extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'cedula',
        'nombre',
        'apellido',
        'email',
        'telefono',
        'carrera',      // texto (compatibilidad con datos antiguos)
        'carrera_id',   // FK a tabla carreras (nuevo)
        'tipo',
        'activo',
        'observaciones'
    ];

    /**
     * Conversión automática de tipos para atributos del modelo.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean'
    ];

    /**
     * Relación: Préstamos donde este estudiante RECIBIÓ el equipo (es el solicitante).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prestamosRecibidos()
    {
        return $this->hasMany(Prestamo::class, 'estudiante_id');
    }

    /**
     * Relación: Préstamos donde este estudiante REGISTRÓ como practicante (quien entrega).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prestamosRegistrados()
    {
        return $this->hasMany(Prestamo::class, 'practicante_id');
    }

    /**
     * Relación: Devoluciones donde este estudiante RECIBIÓ el equipo como practicante (quien recibe).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function devolucionesRecibidas()
    {
        return $this->hasMany(Prestamo::class, 'practicante_recibe_id');
    }

    /**
     * Scope: Filtra únicamente los estudiantes con estado activo.
     *
     * Uso: Estudiante::activos()->get()
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Filtra estudiantes que coincidan con el término de búsqueda.
     *
     * Busca en: cédula, nombre, apellido y email.
     * Uso: Estudiante::buscar('Juan')->get()
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string                                $search Término de búsqueda.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBuscar($query, $search)
    {
        return $query->where('cedula', 'LIKE', "%{$search}%")      // ✅ CAMBIADO
            ->orWhere('nombre', 'LIKE', "%{$search}%")
            ->orWhere('apellido', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%");
    }

    /**
     * Accessor: Devuelve el nombre completo del estudiante (nombre + apellido).
     *
     * Accesible como: $estudiante->nombre_completo
     *
     * @return string
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }

    /**
     * Relación: Un estudiante pertenece a una carrera (por carrera_id).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function carreraRelacion()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }
}