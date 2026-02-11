<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Alumnos;
use App\Models\Ciclos;
use App\Models\Seguimiento;
use App\Models\Empresas;

class SeguimientosApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearAlumnoYEstancia(): array
    {
        // auth
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // empresa
        $empresa = Empresas::factory()->create();

        // instructor
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

        // alumno
        $userAlumno = User::factory()->create(['role' => 'alumno']);

        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
        ]);

        // estancia (campos not null)
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

        return [$alumno->id, $estanciaId];
    }

    public function test_requiere_autenticacion(): void
    {
        $response = $this->getJson('/api/seguimientos/alumno/1');
        $response->assertStatus(401);
    }

    public function test_lista_seguimientos_de_un_alumno(): void
    {
        [$alumnoId, $estanciaId] = $this->crearAlumnoYEstancia();

        DB::table('seguimientos')->insert([
            'accion' => 'Visita empresa',
            'fecha' => now()->toDateString(),
            'descripcion' => 'Todo correcto',
            'via' => 'Presencial',
            'estancia_id' => $estanciaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson("/api/seguimientos/alumno/{$alumnoId}");

        $response->assertStatus(200);
    }

    public function test_crear_nuevo_seguimiento(): void
    {
        [$alumnoId, $estanciaId] = $this->crearAlumnoYEstancia();

        $payload = [
            'accion' => 'Visita empresa',
            'fecha' => now()->toDateString(),
            'descripcion' => 'Todo correcto',
            'via' => 'Presencial',
            'estancia_id' => $estanciaId,

            // lo mando también por si el controller lo usa para buscar estancias
            'alumno_id' => $alumnoId,
        ];

        $response = $this->postJson('/api/nuevo-seguimiento', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('seguimientos', [
            'accion' => 'Visita empresa',
            'estancia_id' => $estanciaId,
        ]);
    }

    public function test_borrar_seguimiento(): void
    {
        [, $estanciaId] = $this->crearAlumnoYEstancia();

        $seguimientoId = DB::table('seguimientos')->insertGetId([
            'accion' => 'Llamada',
            'fecha' => now()->toDateString(),
            'descripcion' => null,
            'via' => 'Teléfono',
            'estancia_id' => $estanciaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/api/seguimientos/{$seguimientoId}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('seguimientos', [
            'id' => $seguimientoId,
        ]);
    }
}
