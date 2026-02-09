<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class TutorSeeder extends Seeder
{
    public function run(): void
    {
        $userTutor = DB::table('users')
            ->where('role', 'tutor_egibide')
            ->first()
            ->id;

        DB::table('tutores')->insert([
            'nombre' => 'Danel',
            'apellidos' => 'Rivas',
            'telefono' => '600333444',
            'alias' => 'U00001',
            'ciudad' => 'Vitoria-Gasteiz',
            'user_id' => $userTutor,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
