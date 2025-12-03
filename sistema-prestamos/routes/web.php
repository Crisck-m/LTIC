<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// 👉 Ruta principal (redirección)
Route::get('/', function () {
    return redirect()->route('login');
});

// 👉 Rutas protegidas por auth
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil del usuario
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');

    // Gestión de Estudiantes
    Route::resource('estudiantes', EstudianteController::class);

    // Gestión de Inventario
    Route::resource('equipos', App\Http\Controllers\EquipoController::class);

    Route::resource('prestamos', App\Http\Controllers\PrestamoController::class);

    // Gestión de Préstamos
    Route::resource('prestamos', PrestamoController::class);

    // Ruta para la bandeja de Devoluciones pendientes
    Route::get('/devoluciones', [App\Http\Controllers\DevolucionController::class, 'index'])->name('devoluciones.index');

    // Gestión de Devoluciones

    // Ruta para VER el formulario de confirmación (Pantalla intermedia)
Route::get('/prestamos/{prestamo}/finalizar', [App\Http\Controllers\PrestamoController::class, 'finalizar'])->name('prestamos.finalizar');

    // Ruta específica para procesar la devolución
    Route::put('/prestamos/{prestamo}/devolver', [App\Http\Controllers\PrestamoController::class, 'devolver'])->name('prestamos.devolver');
});

// ===============================
// 👉 Archivos de Auth (login, logout, register, etc.)
// ===============================
require __DIR__.'/auth.php';
