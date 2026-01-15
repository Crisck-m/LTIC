<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para SQLite, necesitamos recrear la tabla con el nuevo enum
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->enum('tipo', ['estudiante', 'practicante'])->default('estudiante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->enum('tipo', ['estudiante', 'pasante'])->default('estudiante');
        });
    }
};
