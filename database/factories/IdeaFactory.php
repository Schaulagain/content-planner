<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IdeaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'place' => fake()->randomElement(['Boudhanath', 'Swayambhu', 'Phewa Lake', 'Ilam tea garden', 'Nagarkot']),
            'caption' => fake()->sentence(8),
        ];
    }
}
