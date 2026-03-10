<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Creamos la columna (importante que sea uuid)
            $table->uuid('cargo_id')->nullable()->after('password');

            // 2. Creamos la relación física
            $table->foreign('cargo_id')
                  ->references('id')
                  ->on('cargos')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cargo_id']);
            $table->dropColumn('cargo_id');
        });
    }
};