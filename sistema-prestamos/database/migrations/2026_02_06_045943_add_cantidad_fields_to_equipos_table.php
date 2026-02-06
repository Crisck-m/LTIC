<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            // Campo para determinar si es equipo individual o por cantidad
            $table->boolean('es_individual')->default(true)->after('tipo');

            // Cantidad total del equipo (para equipos no individuales)
            $table->integer('cantidad_total')->default(1)->after('es_individual');

            // Cantidad disponible (se decrementa al prestar, incrementa al devolver)
            $table->integer('cantidad_disponible')->default(1)->after('cantidad_total');
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn(['es_individual', 'cantidad_total', 'cantidad_disponible']);
        });
    }
};