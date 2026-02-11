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
}
