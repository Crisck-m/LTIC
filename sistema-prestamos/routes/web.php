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

// ===============================
// 👉 Rutas protegidas por auth (Solo usuarios logueados)
// ===============================
Route::middleware(['auth'])->group(function () {

    // Dashboard (todos)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===============================
    // GESTIÓN DE ESTUDIANTES (todos pueden acceder)
    // ===============================
    Route::resource('estudiantes', EstudianteController::class);
    Route::get('/api/estudiantes/buscar', [EstudianteController::class, 'buscarAjax'])->name('estudiantes.buscar');

    // ===============================
    // GESTIÓN DE EQUIPOS (SOLO ADMIN para crear/editar/eliminar)
    // ===============================

    // Ver inventario y buscar (todos pueden acceder)
    Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos.index');
    Route::get('/api/equipos/buscar', [EquipoController::class, 'buscarAjax'])->name('equipos.buscar');
    Route::get('/api/equipos/estados', [EquipoController::class, 'obtenerEstados'])->name('equipos.estados');

    // Crear, editar y eliminar equipos (SOLO ADMIN)
    Route::middleware(['admin'])->group(function () {
        Route::get('/equipos/create', [EquipoController::class, 'create'])->name('equipos.create');
        Route::post('/equipos', [EquipoController::class, 'store'])->name('equipos.store');
        Route::get('/equipos/{equipo}/edit', [EquipoController::class, 'edit'])->name('equipos.edit');
        Route::put('/equipos/{equipo}', [EquipoController::class, 'update'])->name('equipos.update');
        Route::delete('/equipos/{equipo}', [EquipoController::class, 'destroy'])->name('equipos.destroy');
    });

    // ===============================
    // GESTIÓN DE PRÉSTAMOS (todos pueden acceder)
    // ===============================
    Route::resource('prestamos', PrestamoController::class);

    // Rutas personalizadas de devolución
    Route::get('/devoluciones', [DevolucionController::class, 'index'])->name('devoluciones.index');
    Route::get('/prestamos/{prestamo}/finalizar', [PrestamoController::class, 'finalizar'])->name('prestamos.finalizar');
    Route::put('/prestamos/{prestamo}/devolver', [PrestamoController::class, 'devolver'])->name('prestamos.devolver');

    // Exportación de reportes (SOLO ADMIN)
    Route::middleware(['admin'])->group(function () {
        Route::get('/prestamos/export/pdf', [PrestamoController::class, 'exportPDF'])->name('prestamos.export.pdf');
        Route::get('/prestamos/export/excel', [PrestamoController::class, 'exportExcel'])->name('prestamos.export.excel');
    });
});

// ===============================
// 👉 Archivos de Auth (login, logout, register, etc.)
// ===============================
require __DIR__ . '/auth.php';