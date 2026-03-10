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
        Schema::create('contratos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('persona_id')->constrained('personas')->onDelete('cascade');
            
            $table->decimal('monto_mensual', 15, 2); // Sueldo fijo cada mes
            $table->string('moneda', 5); // BS o USD
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('modalidad'); // Profesional, Juvenil, etc.
            
            // --- CAMBIO AQUÍ: Agregamos el estado ---
            // Usamos 'ACTIVO' por defecto para que las consultas no fallen
            $table->string('estado')->default('ACTIVO')->index(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};