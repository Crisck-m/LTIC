<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        // Insertar carreras iniciales
        DB::table('carreras')->insert([
            ['nombre' => 'Ingeniería de Sistemas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ingeniería Industrial', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ingeniería Civil', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Administración de Empresas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Contabilidad', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Derecho', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Medicina', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Enfermería', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('carreras');
    }
};