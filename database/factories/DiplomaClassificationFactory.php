<?php

namespace Database\Factories;

use App\Models\DiplomaClassification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiplomaClassification>
 */
class DiplomaClassificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'min_cgpa' => 2.00,
            'max_cgpa' => 2.49,
            'classification' => fake()->unique()->word(),
        ];
    }
}
