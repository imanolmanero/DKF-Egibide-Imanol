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
use App\Models\Estancia;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    private function authAsAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        return $admin;
    }

    private function authAsNonAdmin(): User
    {
        $user = User::factory()->create(['role' => 'alumno']);
        Sanctum::actingAs($user);
        return $user;
    }

    public function test_requiere_autenticacion_admin(): void
    {
        $this->getJson('/api/admin/inicio')
            ->assertStatus(401);
    }

    public function test_solo_admin_puede_acceder_inicio_admin(): void
    {
        $this->authAsNonAdmin();

        $response = $this->getJson('/api/admin/inicio');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'No autorizado.',
            ]);
    }

    public function test_inicio_admin_muestra_estadisticas(): void
    {
        $admin = $this->authAsAdmin();

        // Crear datos de prueba
        // Nota: Alumnos::factory()->count(5)->create() crea 5 ciclos también (uno por alumno)
        Alumnos::factory()->count(5)->create();
        Empresas::factory()->count(3)->create();
        Ciclos::factory()->count(2)->create();

        // Total de ciclos: 5 (from alumnos) + 2 (created explicitly) = 7
        // Crear tutores
        $userTutor = User::factory()->create(['role' => 'tutor_egibide']);
        DB::table('tutores')->insert([
            'nombre' => 'Tutor',
            'apellidos' => 'Test',
            'telefono' => '600000000',
            'ciudad' => 'Vitoria',
            'user_id' => $userTutor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/admin/inicio');

        $response->assertOk()
            ->assertJsonStructure([
                'admin' => [
                    'email',
                ],
                'counts' => [
                    'alumnos',
                    'empresas',
                    'tutores',
                    'ciclos',
                ],
            ])
            ->assertJsonFragment([
                'email' => $admin->email,
            ])
            ->assertJsonPath('counts.alumnos', 5)
            ->assertJsonPath('counts.empresas', 3)
            ->assertJsonPath('counts.tutores', 1)
            ->assertJsonPath('counts.ciclos', 7);
    }

    public function test_inicio_admin_con_datos_vacios(): void
    {
        $admin = $this->authAsAdmin();

        $response = $this->getJson('/api/admin/inicio');

        $response->assertOk()
            ->assertJsonPath('counts.alumnos', 0)
            ->assertJsonPath('counts.empresas', 0)
            ->assertJsonPath('counts.tutores', 0)
            ->assertJsonPath('counts.ciclos', 0);
    }

    public function test_detalle_alumno_requiere_rol_admin(): void
    {
        $this->authAsNonAdmin();

        $alumno = Alumnos::factory()->create();

        $response = $this->getJson("/api/admin/alumnos/{$alumno->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'No autorizado.',
            ]);
    }

    public function test_detalle_alumno(): void
    {
        $this->authAsAdmin();

        // Crear estructura completa
        $familia = FamiliaProfesional::factory()->create();
        $ciclo = Ciclos::factory()->create(['familia_profesional_id' => $familia->id]);

        $cursoId = DB::table('cursos')->insertGetId([
            'numero' => 1,
            'ciclo_id' => $ciclo->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
            'nombre' => 'Ana',
            'apellidos' => 'García Pérez',
        ]);

        $empresa = Empresas::factory()->create(['nombre' => 'Tech S.L.']);

        $userTutor = User::factory()->create(['role' => 'tutor_egibide']);
        $tutorId = DB::table('tutores')->insertGetId([
            'nombre' => 'Tutor',
            'apellidos' => 'Test',
            'telefono' => '600000000',
            'ciudad' => 'Vitoria',
            'user_id' => $userTutor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Estancia::create([
            'alumno_id' => $alumno->id,
            'curso_id' => $cursoId,
            'tutor_id' => $tutorId,
            'empresa_id' => $empresa->id,
            'puesto' => 'Desarrollador Junior',
            'fecha_inicio' => now(),
            'horas_totales' => 400,
        ]);

        $response = $this->getJson("/api/admin/alumnos/{$alumno->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'nombre',
                'apellidos',
                'estancias',
            ])
            ->assertJsonFragment([
                'nombre' => 'Ana',
                'apellidos' => 'García Pérez',
            ])
            ->assertJsonPath('estancias.0.empresa.nombre', 'Tech S.L.');
    }

    public function test_detalle_alumno_inexistente_retorna_404(): void
    {
        $this->authAsAdmin();

        $response = $this->getJson('/api/admin/alumnos/999999');

        $response->assertStatus(404);
    }

    public function test_detalle_empresa(): void
    {
        $this->authAsAdmin();

        $empresa = Empresas::factory()->create([
            'nombre' => 'Empresa Test S.L.',
        ]);

        // Crear instructor
        $userInstructor = User::factory()->create(['role' => 'tutor_empresa']);
        DB::table('instructores')->insert([
            'nombre' => 'Carlos',
            'apellidos' => 'Instructor',
            'telefono' => '600111222',
            'ciudad' => 'Bilbao',
            'empresa_id' => $empresa->id,
            'user_id' => $userInstructor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson("/api/admin/empresas/{$empresa->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'nombre' => 'Empresa Test S.L.',
            ])
            ->assertJsonStructure([
                'id',
                'nombre',
                'instructores',
            ]);
    }

    public function test_detalle_empresa_inexistente_retorna_404(): void
    {
        $this->authAsAdmin();

        $response = $this->getJson('/api/admin/empresas/999999');

        $response->assertStatus(404);
    }

    public function test_admin_puede_ver_multiples_estancias_de_alumno(): void
    {
        $this->authAsAdmin();

        $familia = FamiliaProfesional::factory()->create();
        $ciclo = Ciclos::factory()->create(['familia_profesional_id' => $familia->id]);

        $cursoId = DB::table('cursos')->insertGetId([
            'numero' => 1,
            'ciclo_id' => $ciclo->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create(['user_id' => $userAlumno->id]);

        $empresa1 = Empresas::factory()->create(['nombre' => 'Empresa 1']);
        $empresa2 = Empresas::factory()->create(['nombre' => 'Empresa 2']);

        $userTutor = User::factory()->create(['role' => 'tutor_egibide']);
        $tutorId = DB::table('tutores')->insertGetId([
            'nombre' => 'Tutor',
            'apellidos' => 'Test',
            'telefono' => '600000000',
            'ciudad' => 'Vitoria',
            'user_id' => $userTutor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Estancia 1
        Estancia::create([
            'alumno_id' => $alumno->id,
            'curso_id' => $cursoId,
            'tutor_id' => $tutorId,
            'empresa_id' => $empresa1->id,
            'puesto' => 'Junior',
            'fecha_inicio' => now()->subYear(),
            'fecha_fin' => now()->subMonths(6),
            'horas_totales' => 400,
        ]);

        // Estancia 2
        Estancia::create([
            'alumno_id' => $alumno->id,
            'curso_id' => $cursoId,
            'tutor_id' => $tutorId,
            'empresa_id' => $empresa2->id,
            'puesto' => 'Senior',
            'fecha_inicio' => now(),
            'horas_totales' => 400,
        ]);

        $response = $this->getJson("/api/admin/alumnos/{$alumno->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'estancias');
    }
}