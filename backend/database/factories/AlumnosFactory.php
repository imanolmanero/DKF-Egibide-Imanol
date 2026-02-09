<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Alumnos;
use App\Models\User;
use App\Models\Curso;

class AlumnosFactory extends Factory
{
    protected $model = Alumnos::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName() . ' ' . fake()->lastName(),
            'telefono' => fake()->optional()->numerify('#########'),
            'ciudad' => fake()->optional()->city(),
            'user_id' => User::factory(),
            'curso_id' => Curso::factory(),
        ];
    }
}
