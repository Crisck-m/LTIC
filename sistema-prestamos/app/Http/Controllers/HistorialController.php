<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function index()
    {
        // Traemos todo ordenado del más reciente al más antiguo
        $historiales = Historial::with('usuario')->latest()->paginate(20);
        return view('historial.index', compact('historiales'));
    }
}