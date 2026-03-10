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
        Schema::create('primas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contrato_id')->constrained('contratos')->onDelete('cascade');
            $table->string('descripcion'); // Ej: "Prima de fichaje 2026"
            $table->decimal('monto_total', 15, 2);
            $table->date('fecha_pactada'); // Cuándo debería pagarse
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prima');
    }
};
