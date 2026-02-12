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
        Schema::table('estancias', function (Blueprint $table) {
            $table->foreignId('curso_id')->nullable()
                ->constrained('cursos')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estancias', function (Blueprint $table) {
            $table->dropForeignIdFor('cursos');
            $table->dropColumn('curso_id');
        });
    }
};
