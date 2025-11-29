<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    // 1. Mostrar la lista de equipos (Inventario)
    public function index()
    {
        $equipos = Equipo::latest()->paginate(10);
        return view('equipos.index', compact('equipos'));
    }

    // 2. Mostrar el formulario de crear
    public function create()
    {
        return view('equipos.create');
    }

    // 3. Guardar en base de datos
    public function store(Request $request)
    {
        $request->validate([
            'codigo_puce' => 'required|unique:equipos',
            'tipo'        => 'required',
            'marca'       => 'required',
            'modelo'      => 'required',
            'estado'      => 'required',
        ]);

        Equipo::create($request->all());

        return redirect()->route('equipos.index')->with('success', 'Equipo registrado correctamente.');
    }
}