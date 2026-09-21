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
        Schema::create('socio_eventos', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('socio_id')
                ->constrained('socios')
                ->onDelete('cascade');

            $table->foreignUuid('evento_id')
                ->constrained('eventos')
                ->onDelete('cascade');

            $table->boolean('habilitado')->default(true);

            $table->timestamps();

            $table->unique(['socio_id', 'evento_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socio_eventos');
    }
};
