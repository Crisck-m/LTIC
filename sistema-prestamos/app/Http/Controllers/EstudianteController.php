<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstudianteController extends Controller
{
    public function index(Request $request)
    {
        $query = Estudiante::query();

        // 1. Filtro de Búsqueda (Nombre, Apellido o Matrícula)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'LIKE', "%{$request->search}%")
                  ->orWhere('apellido', 'LIKE', "%{$request->search}%")
                  ->orWhere('matricula', 'LIKE', "%{$request->search}%");
            });
        }

        // 2. Filtro por Rol (Tipo)
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $estudiantes = $query->latest()->paginate(10);

        return view('estudiantes.index', compact('estudiantes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricula' => 'required|string|max:20|unique:estudiantes',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:estudiantes',
            'telefono' => 'nullable|string|max:15',
            'carrera' => 'required|string',
            'tipo' => 'required|in:estudiante,pasante',
            'observaciones' => 'nullable|string|max:500'
        ]);

        Estudiante::create($validated);

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante registrado exitosamente.');
    }

    public function show(Estudiante $estudiante)
    {
        return view('estudiantes.show', compact('estudiante'));
    }

    public function edit(Estudiante $estudiante)
    {
        return view('estudiantes.edit', compact('estudiante'));
    }

    public function update(Request $request, Estudiante $estudiante)
    {
        $validated = $request->validate([
            'matricula' => [
                'required',
                'string',
                'max:20',
                Rule::unique('estudiantes')->ignore($estudiante->id)
            ],
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('estudiantes')->ignore($estudiante->id)
            ],
            'telefono' => 'nullable|string|max:15',
            'carrera' => 'required|string',
            'tipo' => 'required|in:estudiante,pasante',
            'activo' => 'boolean',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $estudiante->update($validated);

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante actualizado exitosamente.');
    }

    public function destroy(Estudiante $estudiante)
    {
        // Verificar si el estudiante tiene préstamos activos
        if ($estudiante->prestamosResponsable()->where('estado', 'activo')->exists() ||
            $estudiante->prestamosReceptor()->where('estado', 'activo')->exists()) {
            return redirect()->route('estudiantes.index')
                ->with('error', 'No se puede eliminar el estudiante porque tiene préstamos activos.');
        }

        $estudiante->delete();

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante eliminado exitosamente.');
    }
}