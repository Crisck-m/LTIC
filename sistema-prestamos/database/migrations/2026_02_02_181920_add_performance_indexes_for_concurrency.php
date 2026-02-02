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
        // Índice para búsquedas frecuentes de equipos por estado
        Schema::table('equipos', function (Blueprint $table) {
            $table->index('estado', 'idx_equipos_estado');
        });

        // Índices para filtrado de préstamos
        Schema::table('prestamos', function (Blueprint $table) {
            $table->index('estado', 'idx_prestamos_estado');
            $table->index(['estado', 'fecha_prestamo'], 'idx_prestamos_estado_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropIndex('idx_equipos_estado');
        });

        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropIndex('idx_prestamos_estado');
            $table->dropIndex('idx_prestamos_estado_fecha');
        });
    }
};
