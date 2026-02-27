<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modelo User
 *
 * Representa a un usuario autenticado del sistema (administrador o practicante).
 * Extiende `Authenticatable` de Laravel para manejar autenticación con sesiones.
 *
 * **Roles disponibles:**
 * - `admin`: Acceso completo al sistema.
 * - `practicante`: Acceso limitado para registrar préstamos y devoluciones.
 *
 * @property int         $id       Identificador único.
 * @property string      $name     Nombre completo del usuario.
 * @property string      $email    Correo electrónico del usuario.
 * @property string      $password Contraseña cifrada (oculta en serialización).
 * @property string      $rol      Rol del usuario: 'admin' o 'practicante'.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Campos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
    ];

    /**
     * Campos ocultos en la serialización (JSON / arrays).
     * Evita que la contraseña y el token de sesión sean expuestos accidentalmente.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión automática de tipos para atributos del modelo.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'username_verified_at' => 'datetime',
        'activo' => 'boolean'
    ];

    /**
     * Verificar si el usuario tiene el rol de administrador.
     *
     * @return bool `true` si el usuario es admin, `false` en caso contrario.
     */
    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    /**
     * Verificar si el usuario tiene el rol de practicante.
     *
     * @return bool `true` si el usuario es practicante, `false` en caso contrario.
     */
    public function esPracticante(): bool
    {
        return $this->rol === 'practicante';
    }
}