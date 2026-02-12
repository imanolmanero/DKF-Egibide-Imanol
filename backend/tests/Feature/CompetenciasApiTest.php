<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Alumnos;
use App\Models\Ciclos;
use App\Models\CompetenciaTec;
use App\Models\CompetenciaTransversal;
use App\Models\FamiliaProfesional;
use App\Models\Estancia;
use App\Models\NotaCompetenciaTec;
use App\Models\NotaCompetenciaTransversal;
use App\Models\Empresas;

class CompetenciasApiTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        return $user;
    }

    private function crearEstructuraCompleta(): array
    {
        // Empresa
        $empresa = Empresas::factory()->create();

        // Familia profesional
        $familia = FamiliaProfesional::factory()->create();

        // Ciclo con grupo
        $grupo = fake()->unique()->bothify('DAW##');
        $ciclo = Ciclos::factory()->create([
            'familia_profesional_id' => $familia->id,
            'grupo' => $grupo,
        ]);

        // Instructor
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

        // Alumno
        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
            'grupo' => $grupo,
        ]);

        // Estancia
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
        $this->getJson('/api/competencias')
            ->assertStatus(401);
    }

    public function test_lista_competencias_tecnicas_y_transversales(): void
    {
        $this->authUser();

        $familia = FamiliaProfesional::factory()->create();
        $ciclo = Ciclos::factory()->create(['familia_profesional_id' => $familia->id]);

        CompetenciaTec::create([
            'descripcion' => 'Competencia técnica 1',
            'ciclo_id' => $ciclo->id,
        ]);

        CompetenciaTransversal::create([
            'descripcion' => 'Competencia transversal 1',
            'familia_profesional_id' => $familia->id,
        ]);

        $response = $this->getJson('/api/competencias');

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment([
                'descripcion' => 'Competencia técnica 1',
                'tipo' => 'TECNICA',
            ])
            ->assertJsonFragment([
                'descripcion' => 'Competencia transversal 1',
                'tipo' => 'TRANSVERSAL',
            ]);
    }

    public function test_obtiene_competencias_tecnicas_de_alumno(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraCompleta();

        CompetenciaTec::create([
            'descripcion' => 'Desarrollo web',
            'ciclo_id' => $datos['ciclo']->id,
        ]);

        CompetenciaTec::create([
            'descripcion' => 'Bases de datos',
            'ciclo_id' => $datos['ciclo']->id,
        ]);

        $response = $this->getJson("/api/competenciasTecnicas/alumno/{$datos['alumno']->id}");

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_obtiene_competencias_tecnicas_asignadas(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraCompleta();

        $competencia = CompetenciaTec::create([
            'descripcion' => 'Desarrollo web',
            'ciclo_id' => $datos['ciclo']->id,
        ]);

        NotaCompetenciaTec::create([
            'estancia_id' => $datos['estancia']->id,
            'competencia_tec_id' => $competencia->id,
            'nota' => 3,
        ]);

        $response = $this->getJson("/api/competenciasTecnicas/alumno/{$datos['alumno']->id}/asignadas");

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'descripcion' => 'Desarrollo web',
                'nota' => '3.00',
            ]);
    }

    public function test_obtiene_competencias_transversales_de_alumno(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraCompleta();

        CompetenciaTransversal::create([
            'descripcion' => 'Trabajo en equipo',
            'familia_profesional_id' => $datos['familia']->id,
        ]);

        $response = $this->getJson("/api/competenciasTransversales/alumno/{$datos['alumno']->id}");

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'descripcion' => 'Trabajo en equipo',
            ]);
    }

    public function test_obtiene_calificaciones_tecnicas(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraCompleta();

        $competencia = CompetenciaTec::create([
            'descripcion' => 'Desarrollo web',
            'ciclo_id' => $datos['ciclo']->id,
        ]);

        NotaCompetenciaTec::create([
            'estancia_id' => $datos['estancia']->id,
            'competencia_tec_id' => $competencia->id,
            'nota' => 4,
        ]);

        $response = $this->getJson("/api/competenciasTecnicas/calificaciones/{$datos['alumno']->id}");

        $response->assertOk()
            ->assertJsonCount(1);
    }

    public function test_obtiene_calificaciones_transversales(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraCompleta();

        $competencia = CompetenciaTransversal::create([
            'descripcion' => 'Comunicación',
            'familia_profesional_id' => $datos['familia']->id,
        ]);

        NotaCompetenciaTransversal::create([
            'estancia_id' => $datos['estancia']->id,
            'competencia_trans_id' => $competencia->id,
            'nota' => 3,
        ]);

        $response = $this->getJson("/api/competenciasTransversales/calificaciones/{$datos['alumno']->id}");

        $response->assertOk()
            ->assertJsonCount(1);
    }

    public function test_crear_competencia_tecnica(): void
    {
        $this->authUser();

        $ciclo = Ciclos::factory()->create();

        $payload = [
            'ciclo_id' => $ciclo->id,
            'descripcion' => 'Implementar bases de datos relacionales',
        ];

        $response = $this->postJson('/api/competencia/tecnica', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Competencia técnica agregada correctamente',
            ]);

        $this->assertDatabaseHas('competencias_tec', [
            'descripcion' => 'Implementar bases de datos relacionales',
            'ciclo_id' => $ciclo->id,
        ]);
    }

    public function test_crear_competencia_transversal(): void
    {
        $this->authUser();

        $familia = FamiliaProfesional::factory()->create();

        $payload = [
            'familia_profesional_id' => $familia->id,
            'descripcion' => 'Comunicación efectiva',
        ];

        $response = $this->postJson('/api/competencia/transversal', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Competencia transversal agregada correctamente',
            ]);

        $this->assertDatabaseHas('competencias_trans', [
            'descripcion' => 'Comunicación efectiva',
            'familia_profesional_id' => $familia->id,
        ]);
    }

    public function test_asignar_competencias_tecnicas(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraCompleta();

        $comp1 = CompetenciaTec::create([
            'descripcion' => 'Competencia 1',
            'ciclo_id' => $datos['ciclo']->id,
        ]);

        $comp2 = CompetenciaTec::create([
            'descripcion' => 'Competencia 2',
            'ciclo_id' => $datos['ciclo']->id,
        ]);

        $payload = [
            'alumno_id' => $datos['alumno']->id,
            'competencias' => [$comp1->id, $comp2->id],
        ];

        $response = $this->postJson('/api/competenciasTecnicas/asignar', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Competencias técnicas asignadas correctamente',
            ]);

        $this->assertDatabaseHas('notas_competencia_tec', [
            'estancia_id' => $datos['estancia']->id,
            'competencia_tec_id' => $comp1->id,
        ]);

        $this->assertDatabaseHas('notas_competencia_tec', [
            'estancia_id' => $datos['estancia']->id,
            'competencia_tec_id' => $comp2->id,
        ]);
    }

    public function test_calificar_competencias_tecnicas(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraCompleta();

        $comp1 = CompetenciaTec::create([
            'descripcion' => 'Competencia 1',
            'ciclo_id' => $datos['ciclo']->id,
        ]);

        $payload = [
            'alumno_id' => $datos['alumno']->id,
            'competencias' => [
                [
                    'competencia_id' => $comp1->id,
                    'calificacion' => 3,
                ],
            ],
        ];

        $response = $this->postJson('/api/competenciasTecnicas/calificar', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Competencias técnicas calificadas correctamente',
            ]);

        $this->assertDatabaseHas('notas_competencia_tec', [
            'estancia_id' => $datos['estancia']->id,
            'competencia_tec_id' => $comp1->id,
            'nota' => 3,
        ]);
    }

    public function test_calificar_competencias_transversales(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraCompleta();

        $comp1 = CompetenciaTransversal::create([
            'descripcion' => 'Competencia transversal',
            'familia_profesional_id' => $datos['familia']->id,
        ]);

        $payload = [
            'alumno_id' => $datos['alumno']->id,
            'competencias' => [
                [
                    'competencia_id' => $comp1->id,
                    'calificacion' => 4,
                ],
            ],
        ];

        $response = $this->postJson('/api/competenciasTransversales/calificar', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Competencias transversales calificadas correctamente',
            ]);

        $this->assertDatabaseHas('notas_competencia_trans', [
            'estancia_id' => $datos['estancia']->id,
            'competencia_trans_id' => $comp1->id,
            'nota' => 4,
        ]);
    }

    public function test_valida_calificacion_entre_1_y_4(): void
    {
        $this->authUser();
        $datos = $this->crearEstructuraCompleta();

        $comp = CompetenciaTec::create([
            'descripcion' => 'Test',
            'ciclo_id' => $datos['ciclo']->id,
        ]);

        $payload = [
            'alumno_id' => $datos['alumno']->id,
            'competencias' => [
                [
                    'competencia_id' => $comp->id,
                    'calificacion' => 5, // Fuera de rango
                ],
            ],
        ];

        $response = $this->postJson('/api/competenciasTecnicas/calificar', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['competencias.0.calificacion']);
    }

    public function test_falla_si_alumno_no_tiene_estancia(): void
    {
        $this->authUser();

        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
        ]);

        $response = $this->getJson("/api/competenciasTecnicas/alumno/{$alumno->id}");

        $response->assertStatus(404);
    }
}