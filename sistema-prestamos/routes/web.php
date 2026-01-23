<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\DashboardController;

// 👉 Ruta principal (redirección al login)
Route::get('/', function () {
    return redirect()->route('login');
});

// 👉 Rutas protegidas por auth (Solo usuarios logueados)
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil del usuario
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');

    // Gestión de Estudiantes
    Route::resource('estudiantes', EstudianteController::class);

    // Rutas AJAX para búsqueda en tiempo real (después de las rutas de estudiantes)
    Route::get('/api/estudiantes/buscar', [EstudianteController::class, 'buscarAjax'])->name('estudiantes.buscar');
    Route::get('/api/equipos/buscar', [EquipoController::class, 'buscarAjax'])->name('equipos.buscar');

    // Gestión de Inventario (Equipos)
    Route::resource('equipos', EquipoController::class);

    // Gestión de Préstamos (CRUD completo)
    Route::resource('prestamos', PrestamoController::class);

    // Rutas personalizadas para Devoluciones
    // 1. Bandeja de devoluciones pendientes
    Route::get('/devoluciones', [DevolucionController::class, 'index'])->name('devoluciones.index');

    // 2. Pantalla de confirmación de devolución
    Route::get('/prestamos/{prestamo}/finalizar', [PrestamoController::class, 'finalizar'])->name('prestamos.finalizar');

    // 3. Procesar la devolución (Guardar en BD)
    Route::put('/prestamos/{prestamo}/devolver', [PrestamoController::class, 'devolver'])->name('prestamos.devolver');
});

// ===============================
// 👉 Archivos de Auth (login, logout, register, etc.)
// ===============================
require __DIR__ . '/auth.php';