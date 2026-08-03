<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade índices a la tabla users para optimizar búsquedas
 * y filtros en listados con miles de registros.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Índice para búsquedas y filtros por email
            $table->index('email');

            // Índice para filtros por estado activo/inactivo
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['active']);
        });
    }
};
