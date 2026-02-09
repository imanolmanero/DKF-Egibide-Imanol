<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call([
            CentroSeeder::class,
            FamiliaProfesionalSeeder::class,
            CentroFamiliaProfesionalSeeder::class,
            CicloSeeder::class,
            CursoSeeder::class,

            EmpresaSeeder::class,

            UserSeeder::class,
            TutorSeeder::class,
            CursoTutorSeeder::class,
            InstructorSeeder::class,
            AlumnoSeeder::class,

            AsignaturaSeeder::class,
            ResultadosAprendizajeSeeder::class,
            CompetenciaTransSeeder::class,
            CompetenciaTecSeeder::class,
            CompetenciasTecRaSeeder::class,

            EstanciaSeeder::class,
            CuadernoSeeder::class,
            SeguimientosSeeder::class,
        ]);
    }
}
