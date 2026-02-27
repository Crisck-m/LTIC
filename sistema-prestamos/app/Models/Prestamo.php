<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Prestamo
 *
 * Representa un préstamo de equipos a un estudiante.
 * Cada préstamo puede incluir múltiples equipos, gestionados a través de la
 * tabla intermedia `prestamo_equipos` (modelo `PrestamoEquipo`).
 *
 * **Estados posibles:**
 * - `activo`: El préstamo está en curso, los equipos no han sido devueltos.
 * - `finalizado`: Todos los equipos del préstamo fueron devueltos.
 *
 * @property int         $id                       Identificador único del préstamo.
 * @property int         $estudiante_id            FK al estudiante que recibe los equipos.
 * @property int         $practicante_id           FK al practicante que registra/entrega el préstamo.
 * @property int|null    $user_id                  FK al usuario del sistema que creó el registro.
 * @property \Carbon\Carbon $fecha_prestamo         Fecha y hora en que se realizó el préstamo.
 * @property \Carbon\Carbon $fecha_devolucion_esperada Fecha límite acordada para devolver los equipos.
 * @property string      $estado                   Estado del préstamo ('activo' o 'finalizado').
 * @property string|null $observaciones_prestamo   Notas adicionales registradas al momento del préstamo.
 */
class Prestamo extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'estudiante_id',
        'practicante_id',
        'user_id',
        'fecha_prestamo',
        'fecha_devolucion_esperada',
        'estado',
        'observaciones_prestamo',
    ];

    /**
     * Conversión automática de tipos para atributos de fecha.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_prestamo' => 'datetime',
        'fecha_devolucion_esperada' => 'date',
    ];

    /**
     * Relación: Estudiante que RECIBE el préstamo (solicitante).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    /**
     * Relación: Practicante que REGISTRA el préstamo (quien entrega el equipo).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practicante()
    {
        return $this->belongsTo(Estudiante::class, 'practicante_id');
    }

    /**
     * Relación: Registros de equipos asociados a este préstamo (tabla intermedia).
     *
     * Cada registro en `prestamo_equipos` representa un equipo dentro de este préstamo,
     * incluyendo su estado individual (activo/devuelto), fecha de devolución real, etc.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prestamoEquipos()
    {
        return $this->hasMany(PrestamoEquipo::class);
    }

    /**
     * Relación: Acceso directo a los modelos Equipo (sin la tabla intermedia).
     *
     * Utiliza `hasManyThrough` pasando por `PrestamoEquipo` como tabla intermedia.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function equipos()
    {
        return $this->hasManyThrough(
            Equipo::class,           // Modelo final
            PrestamoEquipo::class,   // Tabla intermedia
            'prestamo_id',           // FK en prestamo_equipos
            'id',                    // FK en equipos
            'id',                    // PK en prestamos
            'equipo_id'              // FK en prestamo_equipos
        );
    }

    /**
     * Relación LEGACY: Compatibilidad con código antiguo que usaba equipo_id directamente en préstamos.
     *
     * @deprecated Usar prestamoEquipos() o equipos() en su lugar.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    /**
     * Método auxiliar: Verifica si todos los equipos del préstamo fueron devueltos.
     *
     * Retorna `true` si no existe ningún registro de equipo con estado distinto a 'devuelto'.
     *
     * @return bool
     */
    public function todosEquiposDevueltos()
    {
        return $this->prestamoEquipos()
            ->where('estado', '!=', 'devuelto')
            ->count() === 0;
    }

    /**
     * Método auxiliar: Obtiene los equipos que aún están activos (no devueltos).
     *
     * Incluye la relación al modelo Equipo para acceder a sus datos.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function equiposActivos()
    {
        return $this->prestamoEquipos()
            ->where('estado', 'activo')
            ->with('equipo')
            ->get();
    }

    /**
     * Método auxiliar: Obtiene los equipos que ya fueron devueltos.
     *
     * Incluye la relación al modelo Equipo para acceder a sus datos.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function equiposDevueltos()
    {
        return $this->prestamoEquipos()
            ->where('estado', 'devuelto')
            ->with('equipo')
            ->get();
    }
}