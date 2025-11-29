<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            
            // RELACIONES (Claves Foráneas)
            $table->foreignId('equipo_id')->constrained('equipos'); 
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->foreignId('user_id')->constrained('users'); // El responsable que registra

            // DATOS DEL PRÉSTAMO
            $table->dateTime('fecha_prestamo');
            $table->dateTime('fecha_devolucion_esperada')->nullable();
            $table->dateTime('fecha_devolucion_real')->nullable();
            
            $table->text('observaciones_prestamo')->nullable();
            $table->text('observaciones_devolucion')->nullable();
            
            $table->string('estado')->default('activo'); // activo, finalizado, atrasado

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};