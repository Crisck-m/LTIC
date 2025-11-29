<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// ===============================
// 👉 Ruta principal (redirección)
// ===============================
Route::get('/', function () {
    return redirect()->route('login');
});

// ===============================
// 👉 Rutas protegidas por auth
// ===============================
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil del usuario
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');

    // Gestión de Estudiantes
    Route::resource('estudiantes', EstudianteController::class);

    Route::resource('equipos', App\Http\Controllers\EquipoController::class);

    Route::resource('prestamos', App\Http\Controllers\PrestamoController::class);

    // Gestión del Inventario
    Route::resource('inventario', InventarioController::class);

    // Gestión de Préstamos
    Route::resource('prestamos', PrestamoController::class);

    // Gestión de Devoluciones
    Route::resource('devoluciones', DevolucionController::class);
});

// ===============================
// 👉 Archivos de Auth (login, logout, register, etc.)
// ===============================
require __DIR__.'/auth.php';
