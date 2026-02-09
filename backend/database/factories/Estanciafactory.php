<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Estancia;
use App\Models\Alumnos;
use App\Models\Empresas;
use App\Models\Curso;

class EstanciaFactory extends Factory
{
    protected $model = Estancia::class;

    public function definition(): array
    {
        $fechaInicio = fake()->dateTimeBetween('-1 year', 'now');
        $fechaFin = fake()->optional()->dateTimeBetween($fechaInicio, '+1 year');

        return [
            'puesto' => fake()->randomElement([
                'Desarrollador Junior',
                'Desarrollador Full Stack',
                'Técnico de Sistemas',
                'Analista',
                'Diseñador Web'
            ]),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'horas_totales' => fake()->numberBetween(300, 500),
            'alumno_id' => Alumnos::factory(),
            'curso_id' => Curso::factory(),
            'empresa_id' => Empresas::factory(),
        ];
    }

    public function activa(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_inicio' => now()->subDays(30),
            'fecha_fin' => null,
        ]);
    }

    public function finalizada(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_inicio' => now()->subMonths(6),
            'fecha_fin' => now()->subMonths(1),
        ]);
    }
}