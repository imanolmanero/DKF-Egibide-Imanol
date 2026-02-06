<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('dni', 10)->unique();
            $table->string('matricula_id');
            $table->string('apellidos', 150);
            $table->string('telefono', 20)->nullable();
            $table->string('ciudad', 120)->nullable();

            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('alumnos');
    }
};
