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
        Schema::create('cuentas_financieras', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('persona_id')->constrained('personas')->onDelete('cascade');
            $table->foreignUuid('banco_id')->constrained('bancos');
            $table->string('identificador_cuenta');
            $table->string('observacion_cuenta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas_financieras');
    }
};
