<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();

            // 1. ¿Qué equipo se presta?
            $table->foreignId('equipo_id')->constrained('equipos');

            // 2. ¿A qué estudiante se le presta? (Quien se lleva el equipo)
            $table->foreignId('estudiante_id')->constrained('estudiantes');

            // 3. ¿Quién REGISTRA el préstamo? (El Pasante/Practicante que atiende)
            // OJO: Lo relacionamos con la tabla 'estudiantes' porque los pasantes están ahí
            $table->foreignId('practicante_id')->constrained('estudiantes');

            // (Opcional) Dejamos el user_id por si quieres saber desde qué cuenta se hizo, o lo quitamos.
            // Por ahora lo dejaremos nullable por si acaso.
            $table->foreignId('user_id')->nullable()->constrained('users');

            // DATOS DEL PRÉSTAMO
            $table->dateTime('fecha_prestamo');
            $table->dateTime('fecha_devolucion_esperada')->nullable();
            $table->dateTime('fecha_devolucion_real')->nullable();

            $table->text('observaciones_prestamo')->nullable();
            $table->text('observaciones_devolucion')->nullable();

            $table->string('estado')->default('activo');

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