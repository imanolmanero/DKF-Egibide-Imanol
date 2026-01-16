<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CentroSeeder::class,
            FamiliaProfesionalSeeder::class,
            CicloSeeder::class,
            CursoSeeder::class,

            EmpresaSeeder::class,

            UserSeeder::class,
            AlumnoSeeder::class,
            TutorSeeder::class,
            InstructorSeeder::class,

            AsignaturaSeeder::class,
            ResultadoAprendizajeSeeder::class,
            CompetenciaTransSeeder::class,
            CompetenciaTecSeeder::class,
            CompetenciaTecRaSeeder::class,

            EstanciaSeeder::class,
            CuadernoSeeder::class,
            SeguimientosSeeder::class,
        ]);
    }
}
