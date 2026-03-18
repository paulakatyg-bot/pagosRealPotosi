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
        // 1. Tabla de Tarjetas / Abonos
        Schema::create('tarjetas_abonos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Código visual con Check Digit (ej: RB-7X92-5)
            $table->string('codigo_visual', 20)->unique()->index();
            
            // Firma HMAC para el QR (almacenamos solo la firma para verificar)
            $table->string('firma_seguridad', 10); 
            
            // Relación con el socio (persona)
            $table->foreignUuid('persona_id')->nullable()->constrained('personas')->onDelete('set null');
            
            $table->enum('estado', ['DISPONIBLE', 'ENTREGADA', 'BLOQUEADA', 'EXTRAVIADA'])
                  ->default('DISPONIBLE')
                  ->index();

            $table->timestamps();
        });

        // 2. Tabla de Ingresos (Para validar que solo entre una vez por partido)
        Schema::create('ingresos_partidos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tarjeta_id')->constrained('tarjetas_abonos');
            // Aquí puedes conectar con una tabla de 'partidos' que crees luego
            $table->string('evento_id')->index(); 
            $table->timestamp('fecha_ingreso');
            
            // Restricción: Una tarjeta, un ingreso por evento
            $table->unique(['tarjeta_id', 'evento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingresos_partidos');
        Schema::dropIfExists('tarjetas_abonos');
    }
};
