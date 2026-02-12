<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

use App\Models\TutorEmpresa;
use App\Models\User;
use App\Models\Empresas;
use App\Models\Alumnos;
use App\Models\Estancia;
use App\Models\Ciclos;
use App\Models\FamiliaProfesional;

class TutorEmpresaModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_tutor_empresa_pertenece_a_una_empresa(): void
    {
        $empresa = Empresas::factory()->create();
        $user = User::factory()->create(['role' => 'tutor_empresa']);

        $tutor = TutorEmpresa::create([
            'nombre' => 'Carlos',
            'apellidos' => 'López',
            'telefono' => '600111222',
            'ciudad' => 'Bilbao',
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($tutor->empresa);
        $this->assertEquals($empresa->id, $tutor->empresa->id);
    }

    public function test_un_tutor_empresa_pertenece_a_un_usuario(): void
    {
        $user = User::factory()->create(['role' => 'tutor_empresa']);
        $empresa = Empresas::factory()->create();

        $tutor = TutorEmpresa::create([
            'nombre' => 'Carlos',
            'apellidos' => 'López',
            'telefono' => '600111222',
            'ciudad' => 'Bilbao',
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($tutor->usuario);
        $this->assertEquals($user->id, $tutor->usuario->id);
    }

    public function test_relacion_empresa_es_belongs_to(): void
    {
        $tutor = new TutorEmpresa();
        $this->assertInstanceOf(BelongsTo::class, $tutor->empresa());
    }

    public function test_relacion_usuario_es_belongs_to(): void
    {
        $tutor = new TutorEmpresa();
        $this->assertInstanceOf(BelongsTo::class, $tutor->usuario());
    }

    public function test_relacion_estancias_es_has_many(): void
    {
        $tutor = new TutorEmpresa();
        $this->assertInstanceOf(HasMany::class, $tutor->estancias());
    }

    public function test_relacion_alumnos_con_estancia_es_belongs_to_many(): void
    {
        $tutor = new TutorEmpresa();
        $this->assertInstanceOf(BelongsToMany::class, $tutor->alumnosConEstancia());
    }

    public function test_se_puede_crear_un_tutor_empresa(): void
    {
        $user = User::factory()->create(['role' => 'tutor_empresa']);
        $empresa = Empresas::factory()->create();

        $tutor = TutorEmpresa::create([
            'nombre' => 'María',
            'apellidos' => 'García Pérez',
            'telefono' => '600222333',
            'ciudad' => 'Vitoria',
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('instructores', [
            'id' => $tutor->id,
            'nombre' => 'María',
            'apellidos' => 'García Pérez',
            'empresa_id' => $empresa->id,
        ]);
    }

    public function test_tutor_empresa_tiene_estancias(): void
    {
        $user = User::factory()->create(['role' => 'tutor_empresa']);
        $empresa = Empresas::factory()->create();

        $tutor = TutorEmpresa::create([
            'nombre' => 'Carlos',
            'apellidos' => 'López',
            'telefono' => '600111222',
            'ciudad' => 'Bilbao',
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
        ]);

        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create(['user_id' => $userAlumno->id]);

        Estancia::create([
            'alumno_id' => $alumno->id,
            'instructor_id' => $tutor->id,
            'empresa_id' => $empresa->id,
            'puesto' => 'Desarrollador',
            'fecha_inicio' => now(),
            'horas_totales' => 400,
        ]);

        $this->assertCount(1, $tutor->estancias);
    }

    public function test_telefono_y_ciudad_son_opcionales(): void
    {
        $user = User::factory()->create(['role' => 'tutor_empresa']);
        $empresa = Empresas::factory()->create();

        $tutor = TutorEmpresa::create([
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('instructores', [
            'nombre' => 'Juan',
        ]);

        $this->assertNull($tutor->telefono);
        $this->assertNull($tutor->ciudad);
    }
}