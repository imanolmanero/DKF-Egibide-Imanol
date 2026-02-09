<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        $userIker = DB::table('users')->where('email', 'iker@demo.com')->value('id');
        $userNaia = DB::table('users')->where('email', 'naia@demo.com')->value('id');
        $userPrueba = DB::table('users')->where('email', 'prueba@test.com')->value('id');

        $cursoId = DB::table('cursos')->first()->id;
        $tutorId = DB::table('tutores')->first()->id;

        DB::table('alumnos')->insert([
            [
                'nombre' => 'Iker',
                'dni' => '12345678A',
                'apellidos' => 'Hernaez',
                'telefono' => '600111222',
                'matricula_id' => '2023-001',
                'ciudad' => 'Vitoria-Gasteiz',
                'user_id' => $userIker,
                'curso_id' => $cursoId,
                'tutor_id' => $tutorId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Naia',
                'dni' => '87654321B',
                'apellidos' => 'Garrido',
                'telefono' => '620111222',
                'matricula_id' => '2023-002',
                'ciudad' => 'Vitoria-Gasteiz',
                'user_id' => $userNaia,
                'curso_id' => $cursoId,
                'tutor_id' => $tutorId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Preueba',
                'dni' => '11111111C',
                'apellidos' => 'dasda',
                'telefono' => '123456789',
                'matricula_id' => '2023-003',
                'ciudad' => 'Vitoria-Gasteiz',
                'user_id' => $userPrueba,
                'curso_id' => $cursoId,
                'tutor_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
