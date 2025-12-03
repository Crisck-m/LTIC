<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Http\Request;

class DevolucionController extends Controller
{
    public function index()
    {
        // Traer SOLO los préstamos que están "En Curso" (activos)
        // Ordenados por fecha: Los más antiguos primero (urgentes)
        $pendientes = Prestamo::with(['equipo', 'estudiante'])
                        ->where('estado', 'activo')
                        ->orderBy('fecha_prestamo', 'asc')
                        ->paginate(10);

        return view('devoluciones.index', compact('pendientes'));
    }
}