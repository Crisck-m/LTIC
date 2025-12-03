<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Equipo;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrestamoController extends Controller
{
    public function index(Request $request)
    {
        // Iniciar la consulta con las relaciones necesarias
        $query = Prestamo::with(['equipo', 'estudiante', 'pasante']);

        // 1. Lógica del Buscador (Search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Buscar por nombre o apellido del estudiante
                $q->whereHas('estudiante', function($subQuery) use ($search) {
                    $subQuery->where('nombre', 'LIKE', "%$search%")
                             ->orWhere('apellido', 'LIKE', "%$search%");
                })
                // O buscar por código o marca del equipo
                ->orWhereHas('equipo', function($subQuery) use ($search) {
                    $subQuery->where('codigo_puce', 'LIKE', "%$search%")
                             ->orWhere('marca', 'LIKE', "%$search%");
                });
            });
        }

        // 2. Lógica del Filtro de Estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Obtener resultados ordenados y paginados
        $prestamos = $query->latest()->paginate(10);
        
        // Retornar la vista conservando los filtros en la URL (para la paginación)
        return view('prestamos.index', compact('prestamos'));
    }

    // 3. Guardar el préstamo y actualizar inventario
    // En la función create()
    public function create()
    {
        $equipos = Equipo::where('estado', 'disponible')->get();
        
        // Estudiantes normales (para prestarles)
        $estudiantes = Estudiante::all(); 

        // Pasantes (para seleccionar quién atiende)
        $pasantes = Estudiante::where('tipo', 'pasante')->get(); // Asegúrate que en tu BD el rol sea 'pasante'

        return view('prestamos.create', compact('equipos', 'estudiantes', 'pasantes'));
    }

    // En la función store()
    public function store(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'equipo_id'     => 'required|exists:equipos,id',
            'pasante_id'    => 'required|exists:estudiantes,id', // Validar que se elija pasante
            'observaciones' => 'nullable|string'
        ]);

        Prestamo::create([
            'equipo_id'     => $request->equipo_id,
            'estudiante_id' => $request->estudiante_id,
            'pasante_id'    => $request->pasante_id, // Guardamos al pasante seleccionado
            'user_id'       => Auth::id(), // Guardamos también la cuenta admin por auditoría técnica
            'fecha_prestamo'=> now(),
            'estado'        => 'activo',
            'observaciones_prestamo' => $request->observaciones
        ]);

        // Cambiar estado del equipo
        $equipo = Equipo::find($request->equipo_id);
        $equipo->estado = 'prestado';
        $equipo->save();

        return redirect()->route('dashboard')->with('success', 'Préstamo registrado correctamente.');
    }

    // 4. Procesar la devolución del equipo
    public function devolver(Prestamo $prestamo)
    {
        // A. Actualizar el Préstamo
        $prestamo->estado = 'finalizado';
        $prestamo->fecha_devolucion_real = now(); // Fecha y hora actual automática
        $prestamo->save();

        // B. Liberar el Equipo (Volver a ponerlo disponible)
        $equipo = $prestamo->equipo;
        $equipo->estado = 'disponible';
        $equipo->save();

        return redirect()->route('prestamos.index')->with('success', 'Equipo devuelto y marcado como disponible correctamente.');
    }

    // 5. Mostrar la pantalla de confirmación de devolución
    public function finalizar(Prestamo $prestamo)
    {
        return view('prestamos.finalizar', compact('prestamo'));
    }

}