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
        Schema::table('tarjetas_abonos', function (Blueprint $table) {

            // 1. Eliminar FK antigua
            $table->dropForeign(['persona_id']);

            // 2. Eliminar columna
            $table->dropColumn('persona_id');
        });

        Schema::table('tarjetas_abonos', function (Blueprint $table) {

            // 3. Crear nueva relación
            $table->foreignUuid('socio_id')
                ->nullable()
                ->after('firma_seguridad')
                ->constrained('socios')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarjetas_abonos', function (Blueprint $table) {
            $table->dropForeign(['socio_id']);
            $table->dropColumn('socio_id');
        });

        Schema::table('tarjetas_abonos', function (Blueprint $table) {
            $table->foreignUuid('persona_id')
                ->nullable()
                ->constrained('personas')
                ->onDelete('set null');
        });
    }
};
