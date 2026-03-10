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
        Schema::create('personas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cargo_id')->constrained('cargos');
            $table->string('nombre');
            $table->string('ci')->unique();
            $table->string('telefono')->nullable();
            $table->string('posicion')->nullable(); 
            $table->enum('nacionalidad', ['NACIONAL','EXTRANJERO'])
            ->default('NACIONAL');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
