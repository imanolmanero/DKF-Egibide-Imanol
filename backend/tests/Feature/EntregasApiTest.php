<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Entrega;
use App\Models\Alumnos;
use App\Models\Ciclos;
use App\Models\Empresas;

class EntregasApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearContextoCompleto(): array
    {
        // Empresa
        $empresa = Empresas::factory()->create();

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
        Sanctum::actingAs($userAlumno);

        $alumno = Alumnos::factory()->create([
            'user_id' => $userAlumno->id,
        ]);

        // Estancia
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

        // Cuaderno prácticas
        $cuadernoId = DB::table('cuadernos_practicas')->insertGetId([
            'estancia_id' => $estanciaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$userAlumno, $cuadernoId];
    }

    public function test_requiere_autenticacion(): void
    {
        $response = $this->getJson('/api/entregas/mias');
        $response->assertStatus(401);
    }

    public function test_listar_mis_entregas(): void
    {
        [$user, $cuadernoId] = $this->crearContextoCompleto();

        Entrega::factory()->create([
            'cuaderno_practicas_id' => $cuadernoId,
        ]);

        $response = $this->getJson('/api/entregas/mias');

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    public function test_crear_entrega_con_archivo(): void
    {
        Storage::fake('public');

        [$user, $cuadernoId] = $this->crearContextoCompleto();

        $file = UploadedFile::fake()->create('entrega.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/entregas', [
            'archivo' => $file,
            'fecha' => now()->toDateString(),
            'cuaderno_practicas_id' => $cuadernoId,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseCount('entregas', 1);

        Storage::disk('public')->assertExists(
            Entrega::first()->archivo
        );
    }

    public function test_borrar_mi_entrega(): void
    {
        [$user, $cuadernoId] = $this->crearContextoCompleto();

        $entrega = Entrega::factory()->create([
            'cuaderno_practicas_id' => $cuadernoId,
        ]);

        $response = $this->deleteJson("/api/entregas/{$entrega->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('entregas', [
            'id' => $entrega->id,
        ]);
    }

    public function test_descargar_archivo_entrega(): void
    {
        Storage::fake('public');

        [$user, $cuadernoId] = $this->crearContextoCompleto();

        $entrega = Entrega::factory()->create([
            'cuaderno_practicas_id' => $cuadernoId,
            'archivo' => 'entregas/test.pdf',
        ]);

        Storage::disk('public')->put('entregas/test.pdf', 'contenido');

        $response = $this->get("/api/entregas/{$entrega->id}/archivo");

        $response->assertStatus(200);
    }
}
