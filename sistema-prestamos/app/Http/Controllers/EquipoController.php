<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Services\EquipoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipoController extends Controller
{
    protected $equipoService;

    public function __construct(EquipoService $equipoService)
    {
        $this->equipoService = $equipoService;
    }

    public function index(Request $request)
    {
        $equipos = $this->equipoService->listarEquipos(
            $request->search,
            $request->estado,
            $request->tipo
        );

        return view('equipos.index', compact('equipos'));
    }

    public function create()
    {
        return view('equipos.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'codigo_puce' => 'required|unique:equipos,codigo_puce',
            'tipo'        => 'required',
            'marca'       => 'required',
            'modelo'      => 'required',
            'estado'      => 'required',
            'detalles'    => 'nullable'
        ]);

        $this->equipoService->crearEquipo($datos);

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo registrado correctamente.');
    }

    public function edit(Equipo $equipo)
    {
        return view('equipos.edit', compact('equipo'));
    }

    public function update(Request $request, Equipo $equipo)
    {
        $datos = $request->validate([
            'codigo_puce' => ['required', Rule::unique('equipos')->ignore($equipo->id)],
            'tipo'        => 'required',
            'marca'       => 'required',
            'modelo'      => 'required',
            'estado'      => 'required',
            'detalles'    => 'nullable'
        ]);

        $this->equipoService->actualizarEquipo($equipo, $datos);

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy(Equipo $equipo)
    {
        $this->equipoService->eliminarEquipo($equipo);
        return redirect()->route('equipos.index')
            ->with('success', 'Equipo eliminado correctamente.');
    }
}