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
        Schema::table('ingresos_partidos', function (Blueprint $table) {
        // Eliminar índice primero
            $table->dropUnique(['tarjeta_id', 'evento_id']);

            // Luego eliminar columna
            $table->dropColumn('evento_id');
        });

        Schema::table('ingresos_partidos', function (Blueprint $table) {
            $table->foreignUuid('evento_id')
                ->after('tarjeta_id')
                ->constrained('eventos')
                ->onDelete('cascade');

            // Volver a crear índice único
            $table->unique(['tarjeta_id', 'evento_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ingresos_partidos', function (Blueprint $table) {
            $table->dropForeign(['evento_id']);
            $table->dropColumn('evento_id');
        });

        Schema::table('ingresos_partidos', function (Blueprint $table) {
            $table->string('evento_id')->nullable();
        });
    }
};
