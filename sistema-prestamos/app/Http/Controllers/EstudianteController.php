<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Services\EstudianteService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * EstudianteController
 *
 * Controlador CRUD para la gestión de estudiantes y practicantes.
 * Permite listar, crear, editar, actualizar y eliminar estudiantes,
 * así como buscarlos vía AJAX para el autocompletado en el formulario de préstamos.
 */
class EstudianteController extends Controller
{
    /**
     * Instancia del servicio de estudiantes.
     *
     * @var EstudianteService
     */
    protected $estudianteService;

    /**
     * Inyecta el servicio de estudiantes al controlador.
     *
     * @param EstudianteService $estudianteService Servicio que encapsula la lógica de negocio para estudiantes.
     */
    public function __construct(EstudianteService $estudianteService)
    {
        $this->estudianteService = $estudianteService;
    }

    /**
     * Muestra el listado de estudiantes con soporte de búsqueda y filtro por tipo.
     *
     * Acepta los parámetros de query:
     * - `search`: texto para filtrar por cédula, nombre, apellido o email.
     * - `tipo`: filtra por 'estudiante' o 'practicante'.
     *
     * @param  Request $request Petición HTTP con los filtros opcionales.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $estudiantes = $this->estudianteService->listarEstudiantes(
            $request->search,
            $request->tipo
        );

        return view('estudiantes.index', compact('estudiantes'));
    }

    /**
     * Muestra el formulario para registrar un nuevo estudiante.
     *
     * Carga la lista de carreras disponibles ordenadas alfabéticamente
     * para mostrarlas en el select del formulario.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $carreras = \App\Models\Carrera::orderBy('nombre')->get();
        return view('estudiantes.create', compact('carreras'));
    }

    /**
     * Almacena un nuevo estudiante en la base de datos.
     *
     * Valida los datos del formulario, resuelve la carrera seleccionada
     * (incluyendo la opción "Otra" que crea automáticamente la carrera si no existe),
     * y crea el registro del estudiante.
     *
     * Reglas de validación principales:
     * - `cedula`: requerida, 10 dígitos, única en la tabla.
     * - `email`: requerido, formato email, único en la tabla.
     * - `tipo`: 'estudiante' o 'practicante'.
     * - `carrera_id`: requerido si no se especifica 'carrera_otra'.
     * - `carrera_otra`: requerido si carrera_id = 'otra'.
     *
     * @param  Request $request Petición HTTP con los datos del formulario.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'cedula' => 'required|digits:10|unique:estudiantes,cedula',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:estudiantes,email',
            'telefono' => 'nullable|digits:10',
            'carrera_id' => 'required_without:carrera_otra',
            'carrera_otra' => 'required_if:carrera_id,otra|nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500',
            'tipo' => 'required|in:estudiante,practicante',
        ], [
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.digits' => 'La cédula debe tener exactamente 10 dígitos numéricos.',
            'cedula.unique' => 'Ya existe un estudiante registrado con esa cédula.',
            'email.unique' => 'Ya existe un estudiante registrado con ese correo electrónico.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'telefono.digits' => 'El teléfono debe tener exactamente 10 dígitos numéricos.',
            'carrera_id.required_without' => 'Debes seleccionar una carrera o especificar una nueva.',
            'carrera_otra.required_if' => 'Debes especificar el nombre de la carrera.',
        ]);

        // ===================================================================
        // LÓGICA: Si seleccionó "Otra", crear la carrera automáticamente
        // ===================================================================
        if ($request->carrera_id === 'otra' && !empty($request->carrera_otra)) {
            $carrera = \App\Models\Carrera::firstOrCreate(
                ['nombre' => trim($request->carrera_otra)]
            );
            $datos['carrera_id'] = $carrera->id;
            $datos['carrera'] = $carrera->nombre;
        } else {
            $carreraObj = \App\Models\Carrera::find($request->carrera_id);
            $datos['carrera'] = $carreraObj ? $carreraObj->nombre : null;
        }

        // Remover campo temporal
        unset($datos['carrera_otra']);

        try {
            Estudiante::create($datos);

            return redirect()->route('estudiantes.index')
                ->with('success', 'Estudiante registrado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Error al registrar el estudiante: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Muestra el formulario de edición de un estudiante existente.
     *
     * Carga la lista de carreras para el select, y pasa el estudiante
     * actual para pre-rellenar el formulario.
     *
     * @param  Estudiante $estudiante El estudiante a editar (resuelto por Route Model Binding).
     * @return \Illuminate\View\View
     */
    public function edit(Estudiante $estudiante)
    {
        $carreras = \App\Models\Carrera::orderBy('nombre')->get();
        return view('estudiantes.edit', compact('estudiante', 'carreras'));
    }

    /**
     * Actualiza los datos de un estudiante existente en la base de datos.
     *
     * Aplica la misma lógica de resolución de carrera que el método `store()`.
     * Ignora la cédula y email propios del estudiante al validar unicidad.
     *
     * @param  Request    $request    Petición HTTP con los datos actualizados.
     * @param  Estudiante $estudiante El estudiante a actualizar (resuelto por Route Model Binding).
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Estudiante $estudiante)
    {
        $datos = $request->validate([
            'cedula' => ['required', 'digits:10', Rule::unique('estudiantes')->ignore($estudiante->id)],
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:100', Rule::unique('estudiantes')->ignore($estudiante->id)],
            'carrera_id' => 'required_without:carrera_otra',
            'carrera_otra' => 'required_if:carrera_id,otra|nullable|string|max:100',
            'tipo' => 'required|in:estudiante,practicante',
            'telefono' => 'nullable|digits:10',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'cedula.digits' => 'La cédula debe tener exactamente 10 dígitos numéricos.',
            'cedula.unique' => 'Ya existe un estudiante registrado con esa cédula.',
            'email.unique' => 'Ya existe un estudiante registrado con ese correo.',
            'telefono.digits' => 'El teléfono debe tener exactamente 10 dígitos numéricos.',
            'carrera_id.required_without' => 'Debes seleccionar una carrera o especificar una nueva.',
            'carrera_otra.required_if' => 'Debes especificar el nombre de la carrera.',
        ]);

        // Resolver carrera_id igual que store()
        if ($request->carrera_id === 'otra' && !empty($request->carrera_otra)) {
            $carrera = \App\Models\Carrera::firstOrCreate(
                ['nombre' => trim($request->carrera_otra)]
            );
            $datos['carrera_id'] = $carrera->id;
            $datos['carrera'] = $carrera->nombre;
        } else {
            $carreraObj = \App\Models\Carrera::find($request->carrera_id);
            $datos['carrera'] = $carreraObj ? $carreraObj->nombre : null;
        }

        unset($datos['carrera_otra']);

        $this->estudianteService->actualizarEstudiante($estudiante, $datos);

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante actualizado correctamente.');
    }

    /**
     * Elimina un estudiante del sistema.
     *
     * Antes de eliminar, verifica (mediante el servicio) si el estudiante
     * tiene préstamos asociados. Si los tiene, la eliminación es bloqueada
     * para preservar la integridad referencial de los datos.
     *
     * @param  Estudiante $estudiante El estudiante a eliminar (resuelto por Route Model Binding).
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Estudiante $estudiante)
    {
        $nombre = "{$estudiante->nombre} {$estudiante->apellido}";
        $cedula = $estudiante->cedula;  // ✅ CAMBIADO

        // Intentamos eliminar usando el servicio
        $eliminado = $this->estudianteService->eliminarEstudiante($estudiante);

        if (!$eliminado) {
            return redirect()->route('estudiantes.index')
                ->with('error', 'No se puede eliminar: El estudiante tiene préstamos asociados.');
        }

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante eliminado correctamente.');
    }

    /**
     * 🔍 Búsqueda AJAX para autocompletado (NUEVO)
     *
     * Retorna hasta 10 estudiantes activos que coincidan con el término de búsqueda.
     * Busca por cédula, nombre, apellido o nombre completo combinado.
     * Utilizado en el formulario de nuevo préstamo para seleccionar estudiantes.
     *
     * @param  Request $request Petición HTTP con el parámetro `q` (término de búsqueda).
     * @return \Illuminate\Http\JsonResponse Lista de estudiantes con id, cédula, nombre, apellido y carrera.
     */
    public function buscarAjax(Request $request)
    {
        $search = $request->get('q');

        $estudiantes = Estudiante::where('activo', true)
            ->where(function ($query) use ($search) {
                $query->where('cedula', 'LIKE', "%{$search}%")  // ✅ CAMBIADO
                    ->orWhere('nombre', 'LIKE', "%{$search}%")
                    ->orWhere('apellido', 'LIKE', "%{$search}%")
                    ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$search}%"]);
            })
            ->limit(10)
            ->get(['id', 'cedula', 'nombre', 'apellido', 'carrera']);  // ✅ CAMBIADO

        return response()->json($estudiantes);
    }
}