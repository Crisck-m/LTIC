<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo PrestamoEquipo
 *
 * Tabla intermedia que representa la asociación entre un préstamo y un equipo específico.
 * Cada registro corresponde a un equipo dentro de un préstamo y almacena su estado
 * individual de devolución, la fecha real de devolución y quién lo recibió.
 *
 * Esto permite devoluciones **parciales**: se pueden devolver algunos equipos de un
 * préstamo sin cerrar el préstamo completo.
 *
 * @property int              $id                      Identificador único del registro.
 * @property int              $prestamo_id             FK al préstamo al que pertenece este equipo.
 * @property int              $equipo_id               FK al equipo prestado.
 * @property int              $cantidad                Cantidad de unidades prestadas de este equipo.
 * @property \Carbon\Carbon|null $fecha_devolucion_real Fecha y hora en que fue devuelto (null si aún activo).
 * @property int|null         $practicante_recibe_id   FK al practicante que recibió la devolución.
 * @property string|null      $observaciones_devolucion Notas registradas al momento de la devolución.
 * @property string           $estado                  Estado: 'activo', 'devuelto' o 'cancelado'.
 */
class PrestamoEquipo extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'prestamo_equipos';

    /**
     * Campos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'prestamo_id',
        'equipo_id',
        'fecha_devolucion_real',
        'practicante_recibe_id',
        'observaciones_devolucion',
        'estado',
    ];

    /**
     * Conversión automática de tipos para atributos de fecha.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_devolucion_real' => 'datetime',
    ];

    /**
     * Relación: El préstamo al que pertenece este equipo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }

    /**
     * Relación: El equipo físico asociado a este registro.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    /**
     * Relación: El practicante que recibió este equipo en la devolución.
     *
     * Puede ser null si el equipo aún no ha sido devuelto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practicanteRecibe()
    {
        return $this->belongsTo(Estudiante::class, 'practicante_recibe_id');
    }
}