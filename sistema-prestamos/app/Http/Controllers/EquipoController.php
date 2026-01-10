<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Services\EquipoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Historial; // Importar Historial

class EquipoController extends Controller
{
    protected $equipoService;

    public function __construct(EquipoService $equipoService)
    {
        $this->equipoService = $equipoService;
    }

    public function index(Request $request)
    {
        $equipos = $this->equipoService->listarEquipos($request->search, $request->estado);
        return view('equipos.index', compact('equipos'));
    }

    public function create()
    {
        return view('equipos.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'tipo' => 'required|string',
            'marca' => 'required|string',
            'modelo' => 'required|string',
            'serie' => 'required|unique:equipos,serie',
            'codigo_puce' => 'required|unique:equipos,codigo_puce',
            'estado' => 'required|in:disponible,prestado,mantenimiento,baja',
            'observaciones' => 'nullable|string'
        ]);

        $equipo = $this->equipoService->crearEquipo($datos);

        Historial::registrar(
            'Nuevo Equipo',
            "Se registró el equipo {$equipo->tipo} {$equipo->marca} con serie {$equipo->serie}."
        );

        return redirect()->route('equipos.index')->with('success', 'Equipo registrado correctamente.');
    }

    public function edit(Equipo $equipo)
    {
        return view('equipos.edit', compact('equipo'));
    }

    public function update(Request $request, Equipo $equipo)
    {
        $datos = $request->validate([
            'tipo' => 'required|string',
            'marca' => 'required|string',
            'modelo' => 'required|string',
            'serie' => ['required', Rule::unique('equipos')->ignore($equipo->id)],
            'codigo_puce' => ['required', Rule::unique('equipos')->ignore($equipo->id)],
            'estado' => 'required|in:disponible,prestado,mantenimiento,baja',
            'observaciones' => 'nullable|string'
        ]);

        $this->equipoService->actualizarEquipo($equipo, $datos);

        Historial::registrar(
            'Equipo Actualizado',
            "Se actualizaron datos del equipo {$equipo->codigo_puce} ({$equipo->marca})."
        );

        return redirect()->route('equipos.index')->with('success', 'Equipo actualizado correctamente.');
    }

    // --- AQUÍ ESTABA PROBABLEMENTE EL PROBLEMA (FALTABA CÓDIGO) ---
    public function destroy(Equipo $equipo)
    {
        $codigo = $equipo->codigo_puce;
        $desc = "{$equipo->tipo} {$equipo->marca}";

        // Intentamos eliminar
        $eliminado = $this->equipoService->eliminarEquipo($equipo);

        if (!$eliminado) {
            return redirect()->route('equipos.index')
                ->with('error', 'No se puede eliminar: El equipo tiene historial de préstamos.');
        }

        Historial::registrar(
            'Equipo Eliminado',
            "Se eliminó del inventario: {$desc} [{$codigo}]."
        );

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo eliminado correctamente.');
    }
}