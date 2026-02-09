<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoSeeder extends Seeder {
    public function run(): void {
        DB::table('cursos')->insert([
            [
                'numero' => 1,
                'id_ciclo' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'numero' => 2,
                'id_ciclo' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
