<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\TutorEgibide;
use App\Models\Ciclos;
use App\Models\FamiliaProfesional;
use App\Models\Alumnos;
use App\Models\Empresas;
use App\Models\TutorEmpresa;
use App\Models\Estancia;
use App\Models\HorarioDia;
use App\Models\HorarioTramo;

class TutorEgibideApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearTutorEgibide(): TutorEgibide
    {
        $user = User::factory()->create(['role' => 'tutor_egibide']);

        $tutor = TutorEgibide::create([
            'nombre' => 'Juan',
            'apellidos' => 'Pérez García',
            'alias' => 'juan_perez',
            'telefono' => '600123456',
            'ciudad' => 'Vitoria',
            'user_id' => $user->id,
        ]);

        return $tutor;
    }

    private function crearEstructuraAlumnoConEmpresa(): array
    {
        $empresa = Empresas::factory()->create();

        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
        ]);

        // Crear instructor
        $userInstructor = User::factory()->create(['role' => 'instructor']);
        $instructor = TutorEmpresa::create([
            'nombre' => 'Carlos',
            'apellidos' => 'García López',
            'telefono' => '600654321',
            'ciudad' => 'Bilbao',
            'empresa_id' => $empresa->id,
            'user_id' => $userInstructor->id,
        ]);

        // Crear estancia
        $estancia = Estancia::create([
            'alumno_id' => $alumno->id,
            'instructor_id' => $instructor->id,
            'empresa_id' => $empresa->id,
            'puesto' => 'Desarrollador',
            'fecha_inicio' => now(),
            'horas_totales' => 400,
        ]);

        return compact('empresa', 'alumno', 'instructor', 'estancia');
    }

    public function test_requiere_autenticacion_para_obtener_tutores_por_ciclo(): void
    {
        $this->getJson('/api/ciclo/1/tutores')
            ->assertStatus(401);
    }

    public function test_obtiene_tutores_de_un_ciclo(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Crear familia profesional con tutor
        $familia = FamiliaProfesional::factory()->create();
        
        $tutor = $this->crearTutorEgibide();
        
        // Asignar tutor a familia (si existe la relación)
        // Ciclo
        $ciclo = Ciclos::factory()->create([
            'familia_profesional_id' => $familia->id,
        ]);

        $response = $this->getJson("/api/ciclo/{$ciclo->id}/tutores");

        $response->assertStatus(200);
    }

    public function test_devuelve_404_si_ciclo_no_existe(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/ciclo/999999/tutores');

        $response->assertStatus(404);
    }

    public function test_requiere_autenticacion_para_inicio_tutor(): void
    {
        $this->getJson('/api/tutorEgibide/inicio')
            ->assertStatus(401);
    }

    public function test_inicio_tutor_sin_tutor_asociado(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tutorEgibide/inicio');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'El usuario no tiene tutor egibide asociado.']);
    }

    public function test_obtiene_perfil_tutor(): void
    {
        $tutor = $this->crearTutorEgibide();
        Sanctum::actingAs($tutor->user);

        $response = $this->getJson('/api/me/tutor-egibide');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'nombre' => 'Juan',
                     'apellidos' => 'Pérez García',
                 ]);
    }

    public function test_obtiene_instructores_para_alumno(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $datos = $this->crearEstructuraAlumnoConEmpresa();

        $response = $this->getJson("/api/alumnos/{$datos['alumno']->id}/instructores");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ])
                 ->assertJsonFragment([
                     'empresa_id' => $datos['empresa']->id,
                 ]);
    }

    public function test_obtiene_instructores_para_alumno_sin_estancia(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
        ]);

        $response = $this->getJson("/api/alumnos/{$alumno->id}/instructores");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => false,
                     'instructores' => [],
                 ]);
    }

    public function test_asignar_instructor_a_alumno(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $datos = $this->crearEstructuraAlumnoConEmpresa();

        // Crear otro instructor
        $userInstructor2 = User::factory()->create(['role' => 'instructor']);
        $instructor2 = TutorEmpresa::create([
            'nombre' => 'Pablo',
            'apellidos' => 'Martínez Ruiz',
            'telefono' => '600999888',
            'ciudad' => 'San Sebastián',
            'empresa_id' => $datos['empresa']->id,
            'user_id' => $userInstructor2->id,
        ]);

        $payload = [
            'alumno_id' => $datos['alumno']->id,
            'instructor_id' => $instructor2->id,
        ];

        $response = $this->postJson('/api/alumnos/asignar-instructor', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Instructor asignado correctamente al alumno',
                 ]);

        $this->assertDatabaseHas('estancias', [
            'id' => $datos['estancia']->id,
            'instructor_id' => $instructor2->id,
        ]);
    }

    public function test_asignar_instructor_valida_campos_requeridos(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/alumnos/asignar-instructor', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['alumno_id', 'instructor_id']);
    }

    public function test_asignar_instructor_falla_si_alumno_no_existe(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/alumnos/asignar-instructor', [
            'alumno_id' => 99999,
            'instructor_id' => 1,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['alumno_id']);
    }

    public function test_asignar_instructor_falla_si_instructor_no_existe(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $datos = $this->crearEstructuraAlumnoConEmpresa();

        $response = $this->postJson('/api/alumnos/asignar-instructor', [
            'alumno_id' => $datos['alumno']->id,
            'instructor_id' => 99999,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['instructor_id']);
    }

    public function test_asignar_instructor_falla_si_no_pertenece_a_empresa(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $datos = $this->crearEstructuraAlumnoConEmpresa();

        // Crear otra empresa
        $empresa2 = Empresas::factory()->create();

        // Crear instructor de otra empresa
        $userInstructor2 = User::factory()->create(['role' => 'instructor']);
        $instructor2 = TutorEmpresa::create([
            'nombre' => 'Pedro',
            'apellidos' => 'Gómez López',
            'telefono' => '600777666',
            'ciudad' => 'Donostia',
            'empresa_id' => $empresa2->id,
            'user_id' => $userInstructor2->id,
        ]);

        $response = $this->postJson('/api/alumnos/asignar-instructor', [
            'alumno_id' => $datos['alumno']->id,
            'instructor_id' => $instructor2->id,
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'El instructor no pertenece a la empresa asignada al alumno',
                 ]);
    }

    public function test_obtiene_horario_alumno(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Crear estructura directamente sin pasar por asignarInstructor
        $empresa = Empresas::factory()->create();
        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
        ]);

        $userInstructor = User::factory()->create(['role' => 'instructor']);
        $instructor = TutorEmpresa::create([
            'nombre' => 'Carlos',
            'apellidos' => 'García López',
            'telefono' => '600654321',
            'ciudad' => 'Bilbao',
            'empresa_id' => $empresa->id,
            'user_id' => $userInstructor->id,
        ]);

        $estancia = Estancia::create([
            'alumno_id' => $alumno->id,
            'instructor_id' => $instructor->id,
            'empresa_id' => $empresa->id,
            'puesto' => 'Desarrollador',
            'fecha_inicio' => now(),
            'horas_totales' => 400,
        ]);

        // Crear horario
        $horarioDia = HorarioDia::create([
            'estancia_id' => $estancia->id,
            'dia_semana' => 'Lunes',
        ]);

        HorarioTramo::create([
            'horario_dia_id' => $horarioDia->id,
            'hora_inicio' => '09:00:00',
            'hora_fin' => '13:00:00',
        ]);

        $response = $this->getJson("/api/horario/{$alumno->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'fecha_inicio',
                     'fecha_fin',
                     'horario'
                 ])
                 ->assertJsonCount(1, 'horario');
    }

    public function test_obtiene_horario_alumno_sin_estancia(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
        ]);

        $response = $this->getJson("/api/horario/{$alumno->id}");

        $response->assertStatus(200);
        // El endpoint devuelve un array/objeto vacío cuando no hay estancia
        $responseData = $response->json();
        $this->assertTrue(empty($responseData) || $responseData === null);
    }
}
