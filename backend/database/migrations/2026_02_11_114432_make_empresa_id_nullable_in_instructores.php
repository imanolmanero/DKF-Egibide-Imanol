<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Hace que empresa_id sea nullable para permitir instructores sin empresa asignada
     */
    public function up(): void
    {
        Schema::table('instructores', function (Blueprint $table) {
            // Eliminar la restricción de foreign key anterior
            $table->dropForeign(['empresa_id']);
            
            // Modificar la columna para que sea nullable
            $table->unsignedBigInteger('empresa_id')->nullable()->change();
            
            // Volver a agregar la foreign key sin restricción de delete
            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->nullOnDelete()  // Si se elimina la empresa, el instructor queda sin empresa
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructores', function (Blueprint $table) {
            // Revertir: eliminar foreign key
            $table->dropForeign(['empresa_id']);
            
            // Hacer que empresa_id no sea nullable
            $table->unsignedBigInteger('empresa_id')->nullable(false)->change();
            
            // Restaurar la foreign key original
            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }
};