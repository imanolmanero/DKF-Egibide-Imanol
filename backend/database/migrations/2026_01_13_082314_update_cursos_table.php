<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Añadir campo id_ciclo a la tabla cursos
 * 
 * Esta migración es CRÍTICA para el sistema de importación.
 * Los grupos del Excel (ej: 131DA) se almacenan en cursos.numero
 * y necesitan estar asociados a un ciclo específico.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            // Añadir campo id_ciclo
            $table->unsignedBigInteger('id_ciclo')->after('numero')->nullable();
            
            // Crear foreign key hacia ciclos
            $table->foreign('id_ciclo', 'fk_cursos_ciclo')
                  ->references('id')
                  ->on('ciclos')
                  ->onDelete('cascade');
            
            // Índice para búsquedas más rápidas
            $table->index('id_ciclo', 'idx_cursos_ciclo');
            
            // Campos opcionales adicionales del CSV
            $table->text('descripcion')->nullable()->after('id_ciclo');
            $table->string('modelo', 10)->nullable()->after('descripcion');
            $table->string('regimen', 10)->nullable()->after('modelo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            // Eliminar foreign key primero
            // Eliminar campos
            $table->dropColumn(['id_ciclo', 'descripcion', 'modelo', 'regimen']);
        });
    }
};