<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('entregas_cuaderno', function (Blueprint $table) {
            $table->id();

            $table->string('titulo', 255);
            $table->text('descripcion')->nullable();
            $table->date('fecha_limite');

            $table->foreignId('ciclo_id')
                ->constrained('ciclos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('tutor_id')
                ->constrained('tutores')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('entregas_cuaderno');
    }
};
