<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use App\Models\Empresas;

class EmpresasApiTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);
    }

    public function test_requiere_autenticacion(): void
    {
        $this->getJson('/api/empresas')
            ->assertStatus(401);
    }

    public function test_lista_empresas(): void
    {
        $this->authUser();

        Empresas::factory()->count(3)->create();

        $this->getJson('/api/empresas')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_crea_una_empresa(): void
    {
        $this->authUser();

        $payload = [
            'nombre' => 'Empresa Test',
            'cif' => 'B12345678',
            'direccion' => 'Calle Falsa 123',
            'telefono' => '600123123',
            'email' => 'empresa@test.com',
        ];

        $this->postJson('/api/empresas', $payload)
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('empresas', [
            'nombre' => 'Empresa Test',
            'cif' => 'B12345678',
            'email' => 'empresa@test.com',
        ]);
    }
}
