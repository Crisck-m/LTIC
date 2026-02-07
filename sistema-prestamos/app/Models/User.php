<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'username_verified_at' => 'datetime',
        'activo' => 'boolean'
    ];

    /**
     * Verificar si el usuario es admin
     */
    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    /**
     * Verificar si el usuario es practicante
     */
    public function esPracticante(): bool
    {
        return $this->rol === 'practicante';
    }
}