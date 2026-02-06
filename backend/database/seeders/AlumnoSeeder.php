<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class AlumnoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void {
    // Asegúrate de que el UserSeeder cree primero estos correos
    $userIker = DB::table('users')->where('email', 'iker@demo.com')->value('id');
    $userNaia = DB::table('users')->where('email', 'naia@demo.com')->value('id');

    DB::table('alumnos')->insert([
        [
            'nombre' => 'Iker',
            'apellidos' => 'Hernaez',
            'dni' => '12345678A',
            'matricula_id' => 'M45022',
            'telefono' => '600111222',
            'ciudad' => 'Vitoria-Gasteiz',
            'user_id' => $userIker,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nombre' => 'Naia',
            'apellidos' => 'Garrido',
            'dni' => '87654321B',
            'matricula_id' => 'M50334',
            'telefono' => '620111222',
            'ciudad' => 'Vitoria-Gasteiz',
            'user_id' => $userNaia,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);
}
}
