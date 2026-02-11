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
        Schema::table('users', function (Blueprint $table) {
            // Modificar el enum para incluir 'instructor'
            $table->enum('role', ['alumno', 'tutor_egibide', 'tutor_empresa', 'instructor', 'admin'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revertir al enum original sin 'instructor'
            $table->enum('role', ['alumno', 'tutor_egibide', 'tutor_empresa', 'admin'])->change();
        });
    }
};
