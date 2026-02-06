<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CicloSeeder extends Seeder {
 public function run(): void {
    DB::table('ciclos')->insert([
        [
            'nombre' => 'Desarrollo de Aplicaciones Web',
            'codigo' => '191ZA', // Código que aparece en tus archivos CSV
            'familia_profesional_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nombre' => 'Electromecánica de Vehículos',
            'codigo' => '131DA', // Otro ejemplo de tu CSV
            'familia_profesional_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}
}
