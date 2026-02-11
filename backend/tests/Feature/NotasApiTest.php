<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Alumnos;
use App\Models\Ciclos;
use App\Models\FamiliaProfesional;
use App\Models\Estancia;
use App\Models\NotaAsignatura;
use App\Models\NotaCompetenciaTec;
use App\Models\NotaCompetenciaTransversal;
use App\Models\NotaCuaderno;
use App\Models\CompetenciaTec;
use App\Models\CompetenciaTransversal;
use App\Models\Empresas;

class NotasApiTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        return $user;
    }

    private function crearEstructuraBasica(): array
    {
        $empresa = Empresas::factory()->create();
        $familia = FamiliaProfesional::factory()->create();
        $ciclo = Ciclos::factory()->create(['familia_profesional_id' => $familia->id]);

        $userInstructor = User::factory()->create(['role' => 'instructor']);
        $instructorId = DB::table('instructores')->insertGetId([
            'nombre' => 'Instructor',
            'apellidos' => 'Test',
            'telefono' => '600000000',
            'ciudad' => 'Vitoria',
            'empresa_id' => $empresa->id,
            'user_id' => $userInstructor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create(['user_id' => $userAlumno->id]);

        $estancia = Estancia::create([
            'alumno_id' => $alumno->id,
            'instructor_id' => $instructorId,
            'empresa_id' => $empresa->id,
            'puesto' => 'Desarrollador',
            'fecha_inicio' => now(),
            'horas_totales' => 400,
        ]);

        return compact('familia', 'ciclo', 'instructorId', 'empresa', 'alumno', 'estancia');
    }

    public function test_requiere_autenticacion(): void
    {
        $this->getJson('/api/notas/alumno/1/tecnicas')
            ->assertStatus(401);
    }

    public function test_obtiene_notas_tecnicas_alumno(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraBasica();

        $competencia = CompetenciaTec::create([
            'descripcion' => 'Desarrollo web',
            'ciclo_id' => $datos['ciclo']->id,
        ]);

        NotaCompetenciaTec::create([
            'estancia_id' => $datos['estancia']->id,
            'competencia_tec_id' => $competencia->id,
            'nota' => 8.5,
        ]);

        $response = $this->getJson("/api/notas/alumno/{$datos['alumno']->id}/tecnicas");

        $response->assertOk()
            ->assertJsonStructure([
                'alumno_id',
                'notas_competenciasTec',
            ]);
    }

    public function test_obtiene_notas_transversales_alumno(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraBasica();

        $competencia = CompetenciaTransversal::create([
            'descripcion' => 'Trabajo en equipo',
            'familia_profesional_id' => $datos['familia']->id,
        ]);

        NotaCompetenciaTransversal::create([
            'estancia_id' => $datos['estancia']->id,
            'competencia_trans_id' => $competencia->id,
            'nota' => 9.0,
        ]);

        $response = $this->getJson("/api/notas/alumno/{$datos['alumno']->id}/transversal");

        $response->assertOk()
            ->assertJsonStructure([
                'estancia_id',
                'nota_media',
            ]);
    }

    public function test_obtiene_notas_egibide_alumno(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraBasica();

        $asignaturaId = DB::table('asignaturas')->insertGetId([
            'codigo_asignatura' => 'BBDD',
            'nombre_asignatura' => 'Bases de Datos',
            'ciclo_id' => $datos['ciclo']->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotaAsignatura::create([
            'alumno_id' => $datos['alumno']->id,
            'asignatura_id' => $asignaturaId,
            'nota' => 7.5,
            'anio' => 2025,
        ]);

        $response = $this->getJson("/api/notas/alumno/{$datos['alumno']->id}/egibide");

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'nota' => '7.50',
                'asignatura_id' => $asignaturaId,
            ]);
    }

    public function test_guarda_notas_egibide(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraBasica();

        $asignaturaId = DB::table('asignaturas')->insertGetId([
            'codigo_asignatura' => 'BBDD',
            'nombre_asignatura' => 'Bases de Datos',
            'ciclo_id' => $datos['ciclo']->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'alumno_id' => $datos['alumno']->id,
            'asignatura_id' => $asignaturaId,
            'nota' => 8.0,
        ];

        $response = $this->postJson('/api/notas/alumno/egibide/guardar', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Nota egibide agregada correctamente',
            ]);

        $this->assertDatabaseHas('notas_asignatura', [
            'alumno_id' => $datos['alumno']->id,
            'asignatura_id' => $asignaturaId,
            'nota' => 8.0,
            'anio' => date('Y'),
        ]);
    }

    public function test_actualiza_notas_egibide_existentes(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraBasica();

        $asignaturaId = DB::table('asignaturas')->insertGetId([
            'codigo_asignatura' => 'BBDD',
            'nombre_asignatura' => 'Bases de Datos',
            'ciclo_id' => $datos['ciclo']->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Primera nota
        NotaAsignatura::create([
            'alumno_id' => $datos['alumno']->id,
            'asignatura_id' => $asignaturaId,
            'nota' => 6.0,
            'anio' => date('Y'),
        ]);

        // Actualizar
        $payload = [
            'alumno_id' => $datos['alumno']->id,
            'asignatura_id' => $asignaturaId,
            'nota' => 8.5,
        ];

        $response = $this->postJson('/api/notas/alumno/egibide/guardar', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('notas_asignatura', [
            'alumno_id' => $datos['alumno']->id,
            'asignatura_id' => $asignaturaId,
            'nota' => 8.5,
        ]);

        // Verificar que solo hay un registro
        $this->assertEquals(1, NotaAsignatura::where([
            'alumno_id' => $datos['alumno']->id,
            'asignatura_id' => $asignaturaId,
            'anio' => date('Y'),
        ])->count());
    }

    public function test_obtiene_nota_cuaderno_alumno(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraBasica();

        $cuadernoId = DB::table('cuadernos_practicas')->insertGetId([
            'estancia_id' => $datos['estancia']->id,
            'archivo' => 'cuaderno.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotaCuaderno::create([
            'cuaderno_practicas_id' => $cuadernoId,
            'nota' => 9.0,
        ]);

        $response = $this->getJson("/api/notas/alumno/{$datos['alumno']->id}/cuaderno");

        $response->assertOk()
            ->assertJsonFragment([
                'nota' => '9.00',
            ]);
    }

    public function test_guarda_nota_cuaderno(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraBasica();

        $cuadernoId = DB::table('cuadernos_practicas')->insertGetId([
            'estancia_id' => $datos['estancia']->id,
            'archivo' => 'cuaderno.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'alumno_id' => $datos['alumno']->id,
            'nota' => 8.5,
        ];

        $response = $this->postJson('/api/notas/alumno/cuaderno/guardar', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Nota del cuaderno guardada correctamente',
            ]);

        $this->assertDatabaseHas('notas_cuaderno', [
            'cuaderno_practicas_id' => $cuadernoId,
            'nota' => 8.5,
        ]);
    }

    public function test_falla_guardar_nota_cuaderno_si_no_existe_cuaderno(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraBasica();

        $payload = [
            'alumno_id' => $datos['alumno']->id,
            'nota' => 8.5,
        ];

        $response = $this->postJson('/api/notas/alumno/cuaderno/guardar', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'El alumno no ha subido su cuaderno de prácticas aún',
            ]);
    }

    public function test_valida_campos_requeridos_notas_egibide(): void
    {
        $this->authUser();

        $response = $this->postJson('/api/notas/alumno/egibide/guardar', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['alumno_id', 'nota', 'asignatura_id']);
    }

    public function test_valida_campos_requeridos_nota_cuaderno(): void
    {
        $this->authUser();

        $response = $this->postJson('/api/notas/alumno/cuaderno/guardar', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['alumno_id', 'nota']);
    }

    public function test_nota_cuaderno_retorna_null_si_no_existe(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraBasica();

        $response = $this->getJson("/api/notas/alumno/{$datos['alumno']->id}/cuaderno");

        $response->assertOk()
            ->assertJson([]);
    }

    public function test_falla_si_alumno_no_tiene_estancia_para_nota_cuaderno(): void
    {
        $this->authUser();

        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create(['user_id' => $userAlumno->id]);

        $response = $this->getJson("/api/notas/alumno/{$alumno->id}/cuaderno");

        $response->assertStatus(404);
    }
}