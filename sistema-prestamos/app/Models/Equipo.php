<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Equipo
 *
 * Representa un equipo del inventario del laboratorio.
 * Soporta dos modalidades:
 * - **Individual** (`es_individual = true`): Equipos identificados de forma única (ej: Laptops).
 *   Tienen nombre/código propio, marca, modelo y su estado cubre todo el equipo.
 * - **Por cantidad** (`es_individual = false`): Equipos agrupados por tipo (ej: audífonos).
 *   Tienen `cantidad_total` y `cantidad_disponible` para controlar el stock.
 *
 * @property int         $id                  Identificador único.
 * @property string      $nombre_equipo       Nombre o código del equipo.
 * @property string      $tipo                Tipo de equipo (Laptop, Audífonos, etc.).
 * @property string|null $tipo_personalizado  Nombre personalizado cuando tipo = 'Otro'.
 * @property bool        $es_individual       Indica si el equipo es una unidad única (laptop) o agrupado.
 * @property int         $cantidad_total      Total de unidades en el inventario.
 * @property int         $cantidad_disponible Unidades actualmente disponibles para préstamo.
 * @property string      $marca               Marca del equipo ('N/A' si no aplica).
 * @property string      $modelo              Modelo del equipo ('N/A' si no aplica).
 * @property string|null $caracteristicas     Descripción técnica o detalles adicionales.
 * @property string      $estado              Estado actual: 'disponible', 'prestado', 'mantenimiento', 'dado_de_baja'.
 * @property int|null    $user_id             ID del usuario que registró el equipo.
 */
class Equipo extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
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

    /**
     * Conversión automática de tipos para atributos del modelo.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'es_individual' => 'boolean',
    ];

    /**
     * Relación: Préstamos asociados a este equipo (a través de la tabla intermedia).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prestamos()
    {
        return $this->hasMany(Prestamo::class);
    }

    /**
     * Scope: Filtra únicamente los equipos con estado 'disponible'.
     *
     * Uso: Equipo::disponibles()->get()
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible');
    }
}