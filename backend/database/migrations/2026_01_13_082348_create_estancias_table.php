<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('estancias', function (Blueprint $table) {
            $table->id();
            $table->string('puesto', 150)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->unsignedInteger('horas_totales')->nullable();

            $table->foreignId('alumno_id')
                ->constrained('alumnos')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreignId('instructor_id')->nullable()
                ->constrained('instructores')
                ->nullOnDelete()->cascadeOnUpdate();

            $table->foreignId('empresa_id')->nullable()
                ->constrained('empresas')
                ->restrictOnDelete()->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('estancias');
    }
};
