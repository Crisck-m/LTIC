<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    // 1. Mostrar la lista de equipos (Inventario)
    public function index(Request $request)
    {
        $query = Equipo::query();

        // 1. Filtro de Búsqueda (Texto)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('codigo_puce', 'LIKE', "%{$request->search}%")
                  ->orWhere('marca', 'LIKE', "%{$request->search}%")
                  ->orWhere('modelo', 'LIKE', "%{$request->search}%");
            });
        }

        // 2. Filtro de Estado (El nuevo botón)
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $equipos = $query->latest()->paginate(10);
        
        // Retornamos la vista (compact envía los datos)
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

    // 4. Mostrar formulario de edición
    public function edit(Equipo $equipo)
    {
        return view('equipos.edit', compact('equipo'));
    }

    // 5. Guardar los cambios (Update)
    public function update(Request $request, Equipo $equipo)
    {
        $request->validate([
            'codigo_puce' => 'required|unique:equipos,codigo_puce,' . $equipo->id, // Ignora su propio ID para que no de error
            'tipo'        => 'required',
            'marca'       => 'required',
            'modelo'      => 'required',
            'estado'      => 'required',
        ]);

        $equipo->update($request->all());

        return redirect()->route('equipos.index')->with('success', 'Equipo actualizado correctamente.');
    }

}