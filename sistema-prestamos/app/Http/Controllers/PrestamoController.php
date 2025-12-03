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
        $query = Prestamo::with(['equipo', 'estudiante', 'responsable']);

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