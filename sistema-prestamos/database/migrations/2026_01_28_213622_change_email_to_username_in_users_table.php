<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Renombrar columna email a username
            $table->renameColumn('email', 'username');

            // Cambiar el índice unique de email a username
            $table->dropUnique(['email']);
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('username', 'email');
            $table->dropUnique(['username']);
            $table->unique('email');
        });
    }
};