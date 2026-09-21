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
        Schema::create('socios', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('persona_id')
                ->constrained('personas')
                ->onDelete('cascade');

            $table->string('codigo_socio')->unique();

            $table->enum('tipo', ['PREFERENCIA', 'CURVAS', 'GENERAL']);

            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'BLOQUEADO'])
                ->default('ACTIVO');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socios');
    }
};
