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
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_puce')->unique(); // El código de barras o activo fijo
            $table->string('tipo'); // Laptop, Proyector, Cable...
            $table->string('marca');
            $table->string('modelo');
            $table->string('estado')->default('disponible'); // disponible, prestado, mantenimiento
            $table->text('caracteristicas')->nullable(); // Ram, Procesador, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};