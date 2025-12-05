<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstudianteController extends Controller
{
    // 1. LISTAR (Con los filtros que agregamos antes)
    public function index(Request $request)
    {
        $query = Estudiante::query();

        // Filtro de Búsqueda
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'LIKE', "%{$request->search}%")
                  ->orWhere('apellido', 'LIKE', "%{$request->search}%")
                  ->orWhere('matricula', 'LIKE', "%{$request->search}%");
            });
        }

        // Filtro por Rol
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $estudiantes = $query->latest()->paginate(10);

        return view('estudiantes.index', compact('estudiantes'));
    }

    // 2. CREAR (Mostrar el formulario) - ¡ESTA ES LA QUE FALTABA!
    public function create()
    {
        return view('estudiantes.create');
    }

    // 3. GUARDAR (Procesar el formulario)
    public function store(Request $request)
    {
        $request->validate([
            'matricula' => 'required|unique:estudiantes,matricula',
            'nombre'    => 'required|string',
            'apellido'  => 'required|string',
            'email'     => 'required|email|unique:estudiantes,email',
            'carrera'   => 'required',
            'tipo'      => 'required|in:estudiante,pasante',
            'telefono'  => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        Estudiante::create($request->all());

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante registrado correctamente.');
    }

    // 4. EDITAR (Mostrar formulario con datos)
    public function edit(Estudiante $estudiante)
    {
        return view('estudiantes.edit', compact('estudiante'));
    }

    // 5. ACTUALIZAR (Guardar cambios)
    public function update(Request $request, Estudiante $estudiante)
    {
        $request->validate([
            'matricula' => ['required', Rule::unique('estudiantes')->ignore($estudiante->id)],
            'nombre'    => 'required|string',
            'apellido'  => 'required|string',
            'email'     => ['required', 'email', Rule::unique('estudiantes')->ignore($estudiante->id)],
            'carrera'   => 'required',
            'tipo'      => 'required|in:estudiante,pasante',
            'telefono'  => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        $estudiante->update($request->all());

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante actualizado correctamente.');
    }

    // 6. ELIMINAR
    public function destroy(Estudiante $estudiante)
    {
        // Opcional: Verificar si tiene préstamos activos antes de borrar
        // if ($estudiante->prestamos()->where('estado', 'activo')->exists()) {
        //    return back()->with('error', 'No se puede eliminar porque tiene préstamos activos.');
        // }

        $estudiante->delete();

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante eliminado correctamente.');
    }
}