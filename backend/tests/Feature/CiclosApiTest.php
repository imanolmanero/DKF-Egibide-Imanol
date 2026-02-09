<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\Ciclos;
use App\Models\Curso;
use App\Models\FamiliaProfesional;
use App\Services\CicloImportService;

class CiclosApiTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        return $user;
    }

    public function test_requiere_autenticacion_en_ciclos_index(): void
    {
        $this->getJson('/api/ciclos')
            ->assertStatus(401);
    }

    public function test_lista_ciclos(): void
    {
        $this->authUser();
        Ciclos::factory()->count(3)->create();

        $this->getJson('/api/ciclos')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_crea_un_ciclo(): void
    {
        $this->authUser();

        // 👇 Creamos la familia para que NO falle la FK
        $familia = FamiliaProfesional::factory()->create();

        $payload = [
            'nombre' => 'DAW',
            'codigo' => 'DAW-2025',
            'familia_profesional_id' => $familia->id,
        ];

        $this->postJson('/api/ciclos', $payload)
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Ciclo agregado',
            ]);

        $this->assertDatabaseHas('ciclos', [
            'nombre' => 'DAW',
            'codigo' => 'DAW-2025',
            'familia_profesional_id' => $familia->id,
        ]);
    }

    public function test_valida_campos_requeridos_al_crear_ciclo(): void
    {
        $this->authUser();

        $this->postJson('/api/ciclos', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre', 'familia_profesional_id']);
    }

    public function test_devuelve_cursos_de_un_ciclo(): void
    {
        $this->authUser();

        $ciclo = Ciclos::factory()->create();

        // 👇 tu BD tiene unique(ciclo_id, numero)
        // así que garantizamos números distintos
        Curso::factory()->create(['ciclo_id' => $ciclo->id, 'numero' => 1]);
        Curso::factory()->create(['ciclo_id' => $ciclo->id, 'numero' => 2]);

        $this->getJson("/api/ciclo/{$ciclo->id}/cursos")
            ->assertOk()
            ->assertJsonCount(2);
    }

    public function test_devuelve_404_si_ciclo_no_existe_al_pedir_cursos(): void
    {
        $this->authUser();

        $this->getJson('/api/ciclo/999999/cursos')
            ->assertStatus(404);
    }

    public function test_devuelve_tutores_de_un_ciclo(): void
    {
        $this->authUser();

        $ciclo = Ciclos::factory()->create();

        $this->getJson("/api/ciclo/{$ciclo->id}/tutores")
            ->assertOk();
    }

    public function test_devuelve_404_si_ciclo_no_existe_al_pedir_tutores(): void
    {
        $this->authUser();

        $this->getJson('/api/ciclo/999999/tutores')
            ->assertStatus(404);
    }

    public function test_descargar_plantilla_csv_devuelve_headers_y_contenido_csv(): void
    {
        $this->authUser();

        $this->mock(CicloImportService::class, function ($mock) {
            $mock->shouldReceive('generarPlantillaCSV')
                ->once()
                ->andReturn("col1,col2\n");
        });

        $response = $this->get('/api/ciclos/plantilla');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertHeader(
            'Content-Disposition',
            'attachment; filename="plantilla_ciclo.csv"'
        );
        $response->assertSee("col1,col2");
    }

    public function test_importar_csv_valida_requeridos_y_mime(): void
    {
        $this->authUser();

        $this->postJson('/api/ciclos/importar', [])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'errors' => ['ciclo_id', 'csv_file'],
            ]);

        $file = UploadedFile::fake()->create('mal.pdf', 10, 'application/pdf');

        $this->postJson('/api/ciclos/importar', [
            'ciclo_id' => 1,
            'csv_file' => $file,
        ])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['csv_file']);
    }

    public function test_importar_csv_ok_llama_al_servicio_y_devuelve_resultado(): void
    {
        $this->authUser();

        $this->mock(CicloImportService::class, function ($mock) {
            $mock->shouldReceive('importarDesdeCSV')
                ->once()
                ->andReturn([
                    'insertados' => 2,
                    'omitidos' => 0,
                ]);
        });

        Storage::fake('local');

        $file = UploadedFile::fake()->create('ciclos.csv', 10, 'text/csv');

        $this->postJson('/api/ciclos/importar', [
            'ciclo_id' => 1,
            'csv_file' => $file,
        ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Importación completada exitosamente',
            ])
            ->assertJsonPath('datos.insertados', 2);
    }

    public function test_importar_csv_si_explota_el_servicio_devuelve_500(): void
    {
        $this->authUser();

        $this->mock(CicloImportService::class, function ($mock) {
            $mock->shouldReceive('importarDesdeCSV')
                ->once()
                ->andThrow(new \Exception('Boom'));
        });

        $file = UploadedFile::fake()->create('ciclos.csv', 10, 'text/csv');

        $this->postJson('/api/ciclos/importar', [
            'ciclo_id' => 1,
            'csv_file' => $file,
        ])
            ->assertStatus(500)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonPath('error', 'Error en la importación: Boom');
    }
}
