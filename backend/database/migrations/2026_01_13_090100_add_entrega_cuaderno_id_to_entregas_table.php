<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('entregas', function (Blueprint $table) {
      $table->foreignId('entrega_cuaderno_id')
        ->nullable()
        ->after('cuaderno_practicas_id')
        ->constrained('entregas_cuaderno')
        ->cascadeOnUpdate()
        ->nullOnDelete();
    });
  }

  public function down(): void {
    Schema::table('entregas', function (Blueprint $table) {
      $table->dropConstrainedForeignId('entrega_cuaderno_id');
    });
  }
};
?>