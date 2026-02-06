<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class TutorSeeder extends Seeder {
    public function run(): void {
        DB::table('tutores')->insert([
            [
                'nombre' => 'Danel',
                'apellidos' => 'Rivas',
                'telefono' => '600333444',
                'ciudad' => 'Vitoria-Gasteiz',
                'alias' => 'U0000',
                'user_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
