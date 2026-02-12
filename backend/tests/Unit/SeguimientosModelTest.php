<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

use App\Models\Seguimiento;
use App\Models\User;
use App\Models\Alumnos;
use App\Models\Ciclos;
use App\Models\Empresas;

class SeguimientosModelTest extends TestCase
{
    use RefreshDatabase;

    private function crearEstancia(): int
    {
        $userTutor = User::factory()->create(['role' => 'tutor_egibide']);

        $empresa = Empresas::factory()->create();

        $instructorId = DB::table('instructores')->insertGetId([
            'nombre' => 'Instructor',
            'apellidos' => 'Pruebas',
            'telefono' => '600000000',
            'ciudad' => 'Vitoria',
            'empresa_id' => $empresa->id,
            'user_id' => $userTutor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userAlumno = User::factory()->create(['role' => 'alumno']);

        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
        ]);

        return DB::table('estancias')->insertGetId([
            'alumno_id' => $alumno->id,
            'instructor_id' => $instructorId,
            'empresa_id' => $empresa->id,
            'puesto' => 'Sin asignar',
            'fecha_inicio' => now()->toDateString(),
            'horas_totales' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_relacion_estancia_es_belongs_to(): void
    {
        $seguimiento = new Seguimiento();
        $this->assertInstanceOf(BelongsTo::class, $seguimiento->estancia());
    }

    public function test_se_puede_crear_un_seguimiento(): void
    {
        $estanciaId = $this->crearEstancia();

        $seguimiento = Seguimiento::create([
            'accion' => 'Visita empresa',
            'fecha' => now()->toDateString(),
            'descripcion' => 'Todo correcto',
            'via' => 'Presencial',
            'estancia_id' => $estanciaId,
        ]);

        $this->assertDatabaseHas('seguimientos', [
            'id' => $seguimiento->id,
            'accion' => 'Visita empresa',
            'estancia_id' => $estanciaId,
        ]);
    }
}
