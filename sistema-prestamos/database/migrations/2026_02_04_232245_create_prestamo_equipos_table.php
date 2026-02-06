<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamo_equipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prestamo_id');
            $table->unsignedBigInteger('equipo_id');
            $table->datetime('fecha_devolucion_real')->nullable();
            $table->unsignedBigInteger('practicante_recibe_id')->nullable();
            $table->text('observaciones_devolucion')->nullable();
            $table->enum('estado', ['activo', 'devuelto', 'cancelado', 'perdido', 'dañado'])->default('activo');
            $table->timestamps();

            // Claves foráneas
            $table->foreign('prestamo_id')->references('id')->on('prestamos')->onDelete('cascade');
            $table->foreign('equipo_id')->references('id')->on('equipos')->onDelete('cascade');
            $table->foreign('practicante_recibe_id')->references('id')->on('estudiantes')->onDelete('set null');

            // Índices para mejorar rendimiento
            $table->index('prestamo_id');
            $table->index('equipo_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamo_equipos');
    }
};