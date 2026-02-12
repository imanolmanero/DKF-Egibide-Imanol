<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Ciclos;
use App\Models\FamiliaProfesional;

class CiclosFactory extends Factory
{
    protected $model = Ciclos::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->word(),
            'familia_profesional_id' => FamiliaProfesional::factory(),
        ];
    }
}
