<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Alumnos;
use App\Models\User;
use App\Models\Curso;

class AlumnosModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_alumno_pertenece_a_un_user(): void
    {
        $user = User::factory()->create();

        $alumno = Alumnos::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($alumno->user);
        $this->assertEquals($user->id, $alumno->user->id);
    }

    public function test_relacion_user_es_belongs_to(): void
    {
        $alumno = new Alumnos();
        $this->assertInstanceOf(BelongsTo::class, $alumno->user());
    }

    public function test_relacion_estancias_es_has_many(): void
    {
        $alumno = new Alumnos();
        $this->assertInstanceOf(HasMany::class, $alumno->estancias());
    }

    public function test_relacion_notas_asignatura_es_has_many(): void
    {
        $alumno = new Alumnos();
        $this->assertInstanceOf(HasMany::class, $alumno->notasAsignatura());
    }

    public function test_se_puede_crear_un_alumno_con_campos_validos(): void
    {
        $user = User::factory()->create();
        $curso = Curso::factory()->create();

        $alumno = Alumnos::create([
            'nombre' => 'Jon',
            'apellidos' => 'Doe Example',
            'telefono' => '600123123',
            'ciudad' => 'Vitoria',
            'user_id' => $user->id,
            'curso_id' => $curso->id,
        ]);

        $this->assertDatabaseHas('alumnos', [
            'id' => $alumno->id,
            'nombre' => 'Jon',
            'apellidos' => 'Doe Example',
            'telefono' => '600123123',
            'ciudad' => 'Vitoria',
            'user_id' => $user->id,
            'curso_id' => $curso->id,
        ]);
    }
}
