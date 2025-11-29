<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Equipo;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrestamoController extends Controller
{
    // 1. Mostrar historial de préstamos
    public function index()
    {
        $prestamos = Prestamo::with(['equipo', 'estudiante', 'responsable'])
                    ->latest()
                    ->paginate(10);
        
        return view('prestamos.index', compact('prestamos'));
    }

    // 2. Mostrar formulario (SOLO equipos disponibles)
    public function create()
    {
        // Traer solo laptops/equipos que estén DISPONIBLES
        $equipos = Equipo::where('estado', 'disponible')->get();
        
        // Traer todos los estudiantes para seleccionar
        $estudiantes = Estudiante::all(); // O Student::all()

        return view('prestamos.create', compact('equipos', 'estudiantes'));
    }

    // 3. Guardar el préstamo y actualizar inventario
    public function store(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id', // O students,id
            'equipo_id'     => 'required|exists:equipos,id',
            'observaciones' => 'nullable|string'
        ]);

        // A. Crear el Préstamo
        Prestamo::create([
            'equipo_id'     => $request->equipo_id,
            'estudiante_id' => $request->estudiante_id,
            'user_id'       => Auth::id(), // El usuario logueado es el responsable
            'fecha_prestamo'=> now(),
            'estado'        => 'activo',
            'observaciones_prestamo' => $request->observaciones
        ]);

        // B. CAMBIAR ESTADO DEL EQUIPO (Para que nadie más lo tome)
        $equipo = Equipo::find($request->equipo_id);
        $equipo->estado = 'prestado';
        $equipo->save();

        return redirect()->route('dashboard')->with('success', 'Préstamo registrado correctamente.');
    }
}