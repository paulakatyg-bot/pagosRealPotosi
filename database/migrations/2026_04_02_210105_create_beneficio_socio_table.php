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
        Schema::create('beneficio_socio', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('socio_id')
                ->constrained('socios')
                ->onDelete('cascade');

            $table->foreignUuid('beneficio_id')
                ->constrained('beneficios')
                ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['socio_id', 'beneficio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficio_socio');
    }
};
