<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

use App\Models\Estancia;
use App\Models\Alumnos;
use App\Models\User;
use App\Models\Empresas;
use App\Models\Ciclos;
use App\Models\FamiliaProfesional;

class EstanciaModelTest extends TestCase
{
    use RefreshDatabase;

    private function crearEstructuraBasica(): array
    {
        $empresa = Empresas::factory()->create();
        
        $userInstructor = User::factory()->create(['role' => 'instructor']);
        $instructorId = DB::table('instructores')->insertGetId([
            'nombre' => 'Instructor',
            'apellidos' => 'Test',
            'telefono' => '600000000',
            'ciudad' => 'Vitoria',
            'empresa_id' => $empresa->id,
            'user_id' => $userInstructor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userAlumno = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create(['user_id' => $userAlumno->id]);

        return compact('instructorId', 'empresa', 'alumno');
    }

    public function test_una_estancia_pertenece_a_un_alumno(): void
    {
        $datos = $this->crearEstructuraBasica();

        $estancia = Estancia::create([
            'alumno_id' => $datos['alumno']->id,
            'instructor_id' => $datos['instructorId'],
            'empresa_id' => $datos['empresa']->id,
            'puesto' => 'Desarrollador',
            'fecha_inicio' => now(),
            'horas_totales' => 400,
        ]);

        $this->assertNotNull($estancia->alumno);
        $this->assertEquals($datos['alumno']->id, $estancia->alumno->id);
    }

    public function test_relacion_alumno_es_belongs_to(): void
    {
        $estancia = new Estancia();
        $this->assertInstanceOf(BelongsTo::class, $estancia->alumno());
    }

    public function test_relacion_tutor_es_belongs_to(): void
    {
        $estancia = new Estancia();
        $this->assertInstanceOf(BelongsTo::class, $estancia->tutor());
    }

    public function test_relacion_instructor_es_belongs_to(): void
    {
        $estancia = new Estancia();
        $this->assertInstanceOf(BelongsTo::class, $estancia->instructor());
    }

    public function test_relacion_empresa_es_belongs_to(): void
    {
        $estancia = new Estancia();
        $this->assertInstanceOf(BelongsTo::class, $estancia->empresa());
    }

    public function test_relacion_curso_es_belongs_to(): void
    {
        $estancia = new Estancia();
        $this->assertInstanceOf(BelongsTo::class, $estancia->curso());
    }

    public function test_relacion_notas_competencia_tec_es_has_many(): void
    {
        $estancia = new Estancia();
        $this->assertInstanceOf(HasMany::class, $estancia->notasCompetenciaTec());
    }

    public function test_relacion_notas_competencia_trans_es_has_many(): void
    {
        $estancia = new Estancia();
        $this->assertInstanceOf(HasMany::class, $estancia->notasCompetenciaTrans());
    }

    public function test_relacion_seguimientos_es_has_many(): void
    {
        $estancia = new Estancia();
        $this->assertInstanceOf(HasMany::class, $estancia->seguimientos());
    }

    public function test_relacion_horarios_dia_es_has_many(): void
    {
        $estancia = new Estancia();
        $this->assertInstanceOf(HasMany::class, $estancia->horariosDia());
    }

    public function test_relacion_cuaderno_practicas_es_has_one(): void
    {
        $estancia = new Estancia();
        $this->assertInstanceOf(HasOne::class, $estancia->cuadernoPracticas());
    }

    public function test_se_puede_crear_una_estancia(): void
    {
        $datos = $this->crearEstructuraBasica();

        $estancia = Estancia::create([
            'alumno_id' => $datos['alumno']->id,
            'instructor_id' => $datos['instructorId'],
            'empresa_id' => $datos['empresa']->id,
            'puesto' => 'Desarrollador Full Stack',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonths(3),
            'horas_totales' => 400,
        ]);

        $this->assertDatabaseHas('estancias', [
            'id' => $estancia->id,
            'alumno_id' => $datos['alumno']->id,
            'puesto' => 'Desarrollador Full Stack',
            'horas_totales' => 400,
        ]);
    }

    public function test_fecha_fin_es_opcional(): void
    {
        $datos = $this->crearEstructuraBasica();

        $estancia = Estancia::create([
            'alumno_id' => $datos['alumno']->id,
            'instructor_id' => $datos['instructorId'],
            'empresa_id' => $datos['empresa']->id,
            'puesto' => 'Desarrollador',
            'fecha_inicio' => now(),
            'horas_totales' => 400,
        ]);

        $this->assertNull($estancia->fecha_fin);
    }

    public function test_fechas_se_castean_a_date(): void
    {
        $datos = $this->crearEstructuraBasica();

        $estancia = Estancia::create([
            'alumno_id' => $datos['alumno']->id,
            'instructor_id' => $datos['instructorId'],
            'empresa_id' => $datos['empresa']->id,
            'puesto' => 'Desarrollador',
            'fecha_inicio' => '2025-01-15',
            'fecha_fin' => '2025-04-15',
            'horas_totales' => 400,
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $estancia->fecha_inicio);
        $this->assertInstanceOf(\Carbon\Carbon::class, $estancia->fecha_fin);
    }
}