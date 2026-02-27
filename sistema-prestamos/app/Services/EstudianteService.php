<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Prestamo;

/**
 * EstudianteService
 *
 * Servicio que encapsula la lógica de negocio para la gestión de estudiantes y practicantes.
 * Centraliza operaciones CRUD y validaciones de negocio (como verificar si un estudiante
 * puede ser eliminado según si tiene préstamos registrados).
 */
class EstudianteService
{
    /**
     * Lista los estudiantes con filtros opcionales y paginación.
     *
     * Aplica el scope `buscar()` del modelo si se proporciona un `$search`,
     * y filtra por tipo ('estudiante' o 'practicante') si se especifica.
     * Resultados ordenados del más reciente al más antiguo, de 10 en 10.
     *
     * @param  string|null $search Texto para buscar en cédula, nombre, apellido o email.
     * @param  string|null $tipo   Filtra por tipo: 'estudiante' o 'practicante'.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listarEstudiantes($search, $tipo)
    {
        $query = Estudiante::query();

        if ($search) {
            $query->buscar($search);  // Usa el scope definido en el modelo
        }

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    /**
     * Crea un nuevo estudiante en la base de datos.
     *
     * @param  array<string, mixed> $datos Datos validados del formulario.
     * @return Estudiante El estudiante recién creado.
     */
    public function crearEstudiante(array $datos)
    {
        return Estudiante::create($datos);
    }

    /**
     * Actualiza los datos de un estudiante existente.
     *
     * @param  Estudiante           $estudiante El estudiante a actualizar.
     * @param  array<string, mixed> $datos      Datos validados del formulario.
     * @return bool `true` si la actualización fue exitosa.
     */
    public function actualizarEstudiante(Estudiante $estudiante, array $datos)
    {
        return $estudiante->update($datos);
    }

    /**
     * Elimina un estudiante del sistema.
     *
     * Antes de eliminar, verifica si el estudiante aparece como solicitante
     * (`estudiante_id`) o como practicante (`practicante_id`) en algún préstamo.
     * Si hay préstamos asociados, la eliminación es bloqueada para preservar
     * la integridad referencial del historial de préstamos.
     *
     * @param  Estudiante $estudiante El estudiante a eliminar.
     * @return bool `true` si fue eliminado, `false` si tiene préstamos asociados.
     */
    public function eliminarEstudiante(Estudiante $estudiante)
    {
        // Verificar si el estudiante aparece en algún préstamo
        $tienePrestamos = Prestamo::where('estudiante_id', $estudiante->id)
            ->orWhere('practicante_id', $estudiante->id)
            ->exists();

        if ($tienePrestamos) {
            return false;
        }

        return $estudiante->delete();
    }
}