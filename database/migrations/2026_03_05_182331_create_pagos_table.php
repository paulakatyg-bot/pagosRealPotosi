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
       Schema::create('pagos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('persona_id')->constrained('personas');
            $table->foreignUuid('contrato_id')->constrained('contratos');
            $table->foreignUuid('prima_id')->nullable()->constrained('primas');

            $table->foreignUuid('cuenta_financiera_id')
                  ->nullable()
                  ->constrained('cuentas_financieras')
                  ->onDelete('set null');
            
            // Identificador del sistema contable externo
            $table->string('comprobante_fise')->nullable()->index(); 

            $table->enum('tipo_pago', ['Sueldo', 'Anticipo Sueldo', 'Prima', 'Anticipo Prima']);
            
            // --- Lógica Contable ---
            $table->decimal('monto_pagado', 15, 2); 
            $table->string('moneda_pago', 5); // <--- ESTA ES LA QUE FALTA
            $table->decimal('tipo_cambio', 15, 6)->default(1);
            
            // El "Debe": Lo que el club reconoce como pagado (en la moneda del contrato)
            $table->decimal('debe_equivalente', 15, 2); 
            
            // El "Haber": Lo que efectivamente salió (en la moneda del contrato)
            $table->decimal('haber_equivalente', 15, 2); 

            $table->date('fecha_operacion');
            $table->string('mes_correspondiente')->nullable()->index(); 
            $table->text('observacion')->nullable(); // <--- TAMBIÉN FALTABA ESTA
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
