<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            // Agregar columna carrera_id
            $table->unsignedBigInteger('carrera_id')->nullable()->after('email');
            $table->foreign('carrera_id')->references('id')->on('carreras')->onDelete('set null');

            // Mantener columna carrera como texto por compatibilidad (opcional)
            // Puedes eliminarla después de migrar todos los datos
        });
    }

    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
            $table->dropColumn('carrera_id');
        });
    }
};