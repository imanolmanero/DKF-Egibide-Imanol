<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Ciclos;
use App\Models\Curso;
use App\Models\FamiliaProfesional;

class CiclosModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_ciclo_pertenece_a_una_familia_profesional(): void
    {
        $familia = FamiliaProfesional::factory()->create();
        $ciclo = Ciclos::factory()->create([
            'familia_profesional_id' => $familia->id,
        ]);

        $this->assertNotNull($ciclo->familiaProfesional);
        $this->assertEquals($familia->id, $ciclo->familiaProfesional->id);
    }

    public function test_un_ciclo_tiene_muchos_cursos(): void
    {
        $ciclo = Ciclos::factory()->create();

        $curso1 = Curso::factory()->create(['ciclo_id' => $ciclo->id, 'numero' => 1]);
        $curso2 = Curso::factory()->create(['ciclo_id' => $ciclo->id, 'numero' => 2]);

        $this->assertCount(2, $ciclo->cursos);
        $this->assertTrue($ciclo->cursos->contains($curso1));
        $this->assertTrue($ciclo->cursos->contains($curso2));
    }

    public function test_se_puede_crear_un_ciclo_con_campos_validos(): void
    {
        $familia = FamiliaProfesional::factory()->create();

        $ciclo = Ciclos::create([
            'nombre' => 'DAW',
            'codigo' => 'DAW-2025',
            'familia_profesional_id' => $familia->id,
        ]);

        $this->assertDatabaseHas('ciclos', [
            'id' => $ciclo->id,
            'nombre' => 'DAW',
            'codigo' => 'DAW-2025',
            'familia_profesional_id' => $familia->id,
        ]);
    }
}
