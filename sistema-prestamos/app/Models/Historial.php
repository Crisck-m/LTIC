<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Historial extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'accion', 'detalles'];

    // Relación: Un historial pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Función mágica para registrar eventos en una línea
    public static function registrar($accion, $detalles)
    {
        return self::create([
            'user_id'  => Auth::check() ? Auth::id() : null,
            'accion'   => $accion,
            'detalles' => $detalles
        ]);
    }
}