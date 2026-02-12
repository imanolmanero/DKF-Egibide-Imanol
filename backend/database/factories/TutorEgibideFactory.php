<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TutorEgibide;
use App\Models\User;

class TutorEgibideFactory extends Factory
{
    protected $model = TutorEgibide::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName() . ' ' . fake()->lastName(),
            'alias' => fake()->unique()->word(),
            'telefono' => fake()->optional()->numerify('#########'),
            'ciudad' => fake()->optional()->city(),
            'user_id' => User::factory(),
        ];
    }
}
