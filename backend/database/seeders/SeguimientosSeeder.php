<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class SeguimientosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('seguimientos')->insert([
            'accion'        => 'Visita de control',
            'fecha'         => date('Y-m-d'),
            'descripcion'   => 'Seguimiento realizado para verificar el estado de la estancia.',
            'via'           => 'Presencial',
            'id_estancia'   => 1, // Asegúrate de que exista
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}
