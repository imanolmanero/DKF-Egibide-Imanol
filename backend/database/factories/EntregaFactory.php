<?php

namespace Database\Factories;

use App\Models\Entrega;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Alumnos;
use App\Models\Ciclos;
use App\Models\Empresas;

class EntregaFactory extends Factory
{
    protected $model = Entrega::class;

    public function definition(): array
    {
        // 1) Crear Ciclo + Curso
        $ciclo = Ciclos::factory()->create();

        $cursoId = DB::table('cursos')->insertGetId([
            'numero' => 1,
            'ciclo_id' => $ciclo->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2) Crear Empresa
        $empresa = Empresas::factory()->create();

        // 3) Crear Instructor (tabla instructores requiere empresa_id)
        $userInstructor = User::factory()->create(['role' => 'instructor']);

        $instructorId = DB::table('instructores')->insertGetId([
            'nombre' => 'Instructor',
            'apellidos' => 'Pruebas',
            'telefono' => '600000000',
            'ciudad' => 'Vitoria',
            'empresa_id' => $empresa->id,
            'user_id' => $userInstructor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4) Crear Alumno
        $userAlumno = User::factory()->create(['role' => 'alumno']);

        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
        ]);

        // 5) Crear Estancia (campos NOT NULL: fecha_inicio, horas_totales)
        $estanciaId = DB::table('estancias')->insertGetId([
            'alumno_id' => $alumno->id,
            'instructor_id' => $instructorId,
            'empresa_id' => $empresa->id,
            'puesto' => 'Sin asignar',
            'fecha_inicio' => now()->toDateString(),
            'horas_totales' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6) Crear Cuaderno de prácticas (NOT NULL: estancia_id)
        $cuadernoId = DB::table('cuadernos_practicas')->insertGetId([
            'estancia_id' => $estanciaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'archivo' => 'entregas/test.pdf',
            'fecha' => now()->toDateString(),
            'cuaderno_practicas_id' => $cuadernoId,
        ];
    }
}
