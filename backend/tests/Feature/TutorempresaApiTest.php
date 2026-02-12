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
use App\Models\Empresas;
use App\Models\TutorEmpresa;
use App\Models\TutorEgibide;
use App\Models\Estancia;

class TutorEmpresaApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearInstructorConEstructura(): array
    {
        // Usuario instructor
        $userInstructor = User::factory()->create(['role' => 'tutor_empresa']);

        // Empresa
        $empresa = Empresas::factory()->create();

        // Instructor
        $instructor = TutorEmpresa::create([
            'nombre' => 'Carlos',
            'apellidos' => 'Martínez López',
            'telefono' => '600111222',
            'ciudad' => 'Bilbao',
            'empresa_id' => $empresa->id,
            'user_id' => $userInstructor->id,
        ]);

        // Estructura para alumnos
        $familia = FamiliaProfesional::factory()->create();
        $ciclo = Ciclos::factory()->create(['familia_profesional_id' => $familia->id]);

        // Usar TutorEgibide factory en lugar de DB::table
        $userTutor = User::factory()->create(['role' => 'tutor_egibide']);
        $tutor = TutorEgibide::create([
            'nombre' => 'Tutor',
            'apellidos' => 'Egibide',
            'alias' => 'tutor_alias',
            'telefono' => '600000000',
            'ciudad' => 'Vitoria',
            'user_id' => $userTutor->id,
        ]);

        return compact('userInstructor', 'instructor', 'empresa', 'ciclo', 'tutor');
    }

    public function test_requiere_autenticacion(): void
    {
        $this->getJson('/api/tutorEmpresa/inicio')
            ->assertStatus(401);
    }

    public function test_inicio_instructor(): void
    {
        $datos = $this->crearInstructorConEstructura();
        Sanctum::actingAs($datos['userInstructor']);

        // Crear alumno con estancia activa
        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create(['user_id' => $userAlumno->id]);

        Estancia::create([
            'alumno_id' => $alumno->id,
            'instructor_id' => $datos['instructor']->id,
            'empresa_id' => $datos['empresa']->id,
            'puesto' => 'Desarrollador Junior',
            'fecha_inicio' => now()->subDays(10),
            'fecha_fin' => now()->addDays(20),
            'horas_totales' => 400,
        ]);

        $response = $this->getJson('/api/tutorEmpresa/inicio');

        $response->assertOk()
            ->assertJsonStructure([
                'instructor' => [
                    'nombre',
                    'apellidos',
                    'telefono',
                    'ciudad',
                    'email',
                ],
                'counts' => [
                    'alumnos_asignados',
                ],
            ])
            ->assertJsonFragment([
                'nombre' => 'Carlos',
                'apellidos' => 'Martínez López',
                'alumnos_asignados' => 1,
            ]);
    }

    public function test_inicio_instructor_sin_estancias_activas(): void
    {
        $datos = $this->crearInstructorConEstructura();
        Sanctum::actingAs($datos['userInstructor']);

        $response = $this->getJson('/api/tutorEmpresa/inicio');

        $response->assertOk()
            ->assertJsonFragment([
                'alumnos_asignados' => 0,
            ]);
    }

    public function test_inicio_instructor_cuenta_solo_estancias_activas(): void
    {
        $datos = $this->crearInstructorConEstructura();
        Sanctum::actingAs($datos['userInstructor']);

        // Alumno con estancia pasada (no debe contar)
        $userAlumno1 = User::factory()->create(['role' => 'alumno']);
        $alumno1 = Alumnos::factory()->create(['user_id' => $userAlumno1->id]);

        Estancia::create([
            'alumno_id' => $alumno1->id,
            'instructor_id' => $datos['instructor']->id,
            'empresa_id' => $datos['empresa']->id,
            'puesto' => 'Desarrollador',
            'fecha_inicio' => now()->subDays(60),
            'fecha_fin' => now()->subDays(30),
            'horas_totales' => 400,
        ]);

        // Alumno con estancia activa (debe contar)
        $userAlumno2 = User::factory()->create(['role' => 'alumno']);
        $alumno2 = Alumnos::factory()->create(['user_id' => $userAlumno2->id]);

        Estancia::create([
            'alumno_id' => $alumno2->id,
            'instructor_id' => $datos['instructor']->id,
            'empresa_id' => $datos['empresa']->id,
            'puesto' => 'Desarrollador',
            'fecha_inicio' => now()->subDays(10),
            'fecha_fin' => null,
            'horas_totales' => 400,
        ]);

        $response = $this->getJson('/api/tutorEmpresa/inicio');

        $response->assertOk()
            ->assertJsonFragment([
                'alumnos_asignados' => 1,
            ]);
    }

    public function test_inicio_instructor_falla_si_usuario_no_tiene_instructor(): void
    {
        $user = User::factory()->create(['role' => 'alumno']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tutorEmpresa/inicio');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'El usuario no tiene instructor (tutor de empresa) asociado.',
            ]);
    }

    public function test_obtiene_alumnos_asignados_instructor(): void
    {
        $datos = $this->crearInstructorConEstructura();
        Sanctum::actingAs($datos['userInstructor']);

        // Crear alumno asignado
        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
            'nombre' => 'Ana',
            'apellidos' => 'García',
        ]);

        Estancia::create([
            'alumno_id' => $alumno->id,
            'instructor_id' => $datos['instructor']->id,
            'empresa_id' => $datos['empresa']->id,
            'puesto' => 'Desarrollador',
            'fecha_inicio' => now(),
            'horas_totales' => 400,
        ]);

        $response = $this->getJson("/api/tutorEmpresa/{$datos['instructor']->id}/alumnos");

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'nombre' => 'Ana',
                'apellidos' => 'García',
            ]);
    }

    public function test_instructor_no_ve_alumnos_de_otros_instructores(): void
    {
        $datos = $this->crearInstructorConEstructura();
        Sanctum::actingAs($datos['userInstructor']);

        // Crear otro instructor
        $userOtroInstructor = User::factory()->create(['role' => 'tutor_empresa']);
        $otroInstructor = TutorEmpresa::create([
            'nombre' => 'Otro',
            'apellidos' => 'Instructor',
            'telefono' => '600222333',
            'ciudad' => 'Vitoria',
            'empresa_id' => $datos['empresa']->id,
            'user_id' => $userOtroInstructor->id,
        ]);

        // Alumno del otro instructor
        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create(['user_id' => $userAlumno->id]);

        Estancia::create([
            'alumno_id' => $alumno->id,
            'instructor_id' => $otroInstructor->id,
            'empresa_id' => $datos['empresa']->id,
            'puesto' => 'Desarrollador',
            'fecha_inicio' => now(),
            'horas_totales' => 400,
        ]);

        $response = $this->getJson("/api/tutorEmpresa/{$datos['instructor']->id}/alumnos");

        $response->assertOk()
            ->assertJsonCount(0);
    }

    public function test_obtiene_perfil_instructor(): void
    {
        $datos = $this->crearInstructorConEstructura();
        Sanctum::actingAs($datos['userInstructor']);

        $response = $this->getJson('/api/me/tutor-empresa');

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'nombre',
                'apellidos',
                'email',
                'tipo',
            ])
            ->assertJsonFragment([
                'nombre' => 'Carlos',
                'apellidos' => 'Martínez López',
                'email' => $datos['userInstructor']->email,
            ]);
    }

    public function test_falla_obtener_perfil_si_no_es_instructor(): void
    {
        $user = User::factory()->create(['role' => 'alumno']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me/tutor-empresa');

        $response->assertStatus(500);
    }

    // Tests para las nuevas rutas

    public function test_obtiene_lista_instructores(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);

        // Crear varios instructores
        $empresa = Empresas::factory()->create();
        
        for ($i = 0; $i < 3; $i++) {
            $userInstructor = User::factory()->create(['role' => 'tutor_empresa']);
            TutorEmpresa::create([
                'nombre' => 'Instructor' . $i,
                'apellidos' => 'Test' . $i,
                'telefono' => '600' . $i,
                'ciudad' => 'City' . $i,
                'empresa_id' => $empresa->id,
                'user_id' => $userInstructor->id,
            ]);
        }

        $response = $this->getJson('/api/instructores');

        $response->assertOk()
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'nombre',
                    'apellidos',
                    'telefono',
                    'ciudad',
                    'empresa_id',
                    'user_id',
                ]
            ]);
    }

    public function test_obtiene_instructores_disponibles(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);

        $empresa = Empresas::factory()->create();
        
        // Instructor con empresa
        $userInstructor1 = User::factory()->create(['role' => 'tutor_empresa']);
        $instructor1 = TutorEmpresa::create([
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
            'telefono' => '600111111',
            'ciudad' => 'Madrid',
            'empresa_id' => $empresa->id,
            'user_id' => $userInstructor1->id,
        ]);

        // Instructor disponible (sin empresa)
        $userInstructor2 = User::factory()->create(['role' => 'tutor_empresa']);
        $instructor2 = TutorEmpresa::create([
            'nombre' => 'María',
            'apellidos' => 'García',
            'telefono' => '600222222',
            'ciudad' => 'Barcelona',
            'empresa_id' => null,
            'user_id' => $userInstructor2->id,
        ]);

        $response = $this->getJson('/api/instructores/disponibles');

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'nombre',
                    'apellidos',
                    'empresa',
                    'usuario',
                ]
            ]);
    }

    public function test_crear_instructor(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);

        $empresa = Empresas::factory()->create();

        $response = $this->postJson('/api/instructores/crear', [
            'empresa_id' => $empresa->id,
            'nombre' => 'Nuevo',
            'apellidos' => 'Instructor',
            'email' => 'nuevo@example.com',
            'telefono' => '600333333',
            'ciudad' => 'Valencia',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'nombre' => 'Nuevo',
                'apellidos' => 'Instructor',
            ]);

        // Verificar que el usuario fue creado
        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@example.com',
            'role' => 'instructor',
        ]);

        // Verificar que el instructor fue creado
        $this->assertDatabaseHas('instructores', [
            'nombre' => 'Nuevo',
            'apellidos' => 'Instructor',
            'empresa_id' => $empresa->id,
        ]);
    }

    public function test_crear_instructor_falla_sin_email_unico(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);

        $empresa = Empresas::factory()->create();
        
        // Email duplicado
        User::factory()->create(['email' => 'duplicado@example.com']);

        $response = $this->postJson('/api/instructores/crear', [
            'empresa_id' => $empresa->id,
            'nombre' => 'Nuevo',
            'apellidos' => 'Instructor',
            'email' => 'duplicado@example.com',
            'telefono' => '600333333',
            'ciudad' => 'Valencia',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_crear_instructor_desasigna_anterior(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);

        $empresa = Empresas::factory()->create();

        // Crear instructor anterior
        $userAnterior = User::factory()->create(['role' => 'tutor_empresa']);
        TutorEmpresa::create([
            'nombre' => 'Anterior',
            'apellidos' => 'Instructor',
            'telefono' => '600111111',
            'ciudad' => 'Madrid',
            'empresa_id' => $empresa->id,
            'user_id' => $userAnterior->id,
        ]);

        // Crear nuevo instructor
        $response = $this->postJson('/api/instructores/crear', [
            'empresa_id' => $empresa->id,
            'nombre' => 'Nuevo',
            'apellidos' => 'Instructor',
            'email' => 'nuevo@example.com',
            'telefono' => '600333333',
            'ciudad' => 'Valencia',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);

        // Verificar que el instructor anterior fue desasignado
        $this->assertDatabaseHas('instructores', [
            'nombre' => 'Anterior',
            'empresa_id' => null,
        ]);

        // Verificar que el nuevo instructor está asignado
        $this->assertDatabaseHas('instructores', [
            'nombre' => 'Nuevo',
            'empresa_id' => $empresa->id,
        ]);
    }

    public function test_asignar_instructor(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);

        $empresa1 = Empresas::factory()->create();
        $empresa2 = Empresas::factory()->create();

        // Crear instructor sin empresa
        $userInstructor = User::factory()->create(['role' => 'tutor_empresa']);
        $instructor = TutorEmpresa::create([
            'nombre' => 'Instructor',
            'apellidos' => 'Test',
            'telefono' => '600111111',
            'ciudad' => 'Madrid',
            'empresa_id' => null,
            'user_id' => $userInstructor->id,
        ]);

        $response = $this->postJson('/api/instructores/asignar', [
            'instructor_id' => $instructor->id,
            'empresa_id' => $empresa1->id,
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'nombre' => 'Instructor',
            ]);

        // Verificar asignación
        $this->assertDatabaseHas('instructores', [
            'id' => $instructor->id,
            'empresa_id' => $empresa1->id,
        ]);
    }

    public function test_asignar_instructor_desasigna_anterior(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);

        $empresa1 = Empresas::factory()->create();
        $empresa2 = Empresas::factory()->create();

        // Instructor antiguo en empresa2
        $userAntiguo = User::factory()->create(['role' => 'tutor_empresa']);
        $instructorAntiguo = TutorEmpresa::create([
            'nombre' => 'Antiguo',
            'apellidos' => 'Instructor',
            'telefono' => '600111111',
            'ciudad' => 'Madrid',
            'empresa_id' => $empresa2->id,
            'user_id' => $userAntiguo->id,
        ]);

        // Instructor nuevo sin empresa
        $userNuevo = User::factory()->create(['role' => 'tutor_empresa']);
        $instructorNuevo = TutorEmpresa::create([
            'nombre' => 'Nuevo',
            'apellidos' => 'Instructor',
            'telefono' => '600222222',
            'ciudad' => 'Barcelona',
            'empresa_id' => null,
            'user_id' => $userNuevo->id,
        ]);

        // Asignar nuevo instructor a empresa2
        $response = $this->postJson('/api/instructores/asignar', [
            'instructor_id' => $instructorNuevo->id,
            'empresa_id' => $empresa2->id,
        ]);

        $response->assertOk();

        // Verificar que el antiguo fue desasignado
        $this->assertDatabaseHas('instructores', [
            'id' => $instructorAntiguo->id,
            'empresa_id' => null,
        ]);

        // Verificar que el nuevo está asignado
        $this->assertDatabaseHas('instructores', [
            'id' => $instructorNuevo->id,
            'empresa_id' => $empresa2->id,
        ]);
    }

    public function test_asignar_instructor_falla_si_no_existe(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);

        $empresa = Empresas::factory()->create();

        $response = $this->postJson('/api/instructores/asignar', [
            'instructor_id' => 99999,
            'empresa_id' => $empresa->id,
        ]);

        $response->assertStatus(422);
    }
}