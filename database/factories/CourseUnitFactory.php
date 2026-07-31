<?php

namespace Database\Factories;

use App\Models\CourseUnit;
use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseUnit>
 */
class CourseUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'programme_id' => Programme::factory(),
            'code' => strtoupper(fake()->unique()->lexify('??? ###')),
            'title' => ucfirst(fake()->unique()->words(3, true)),
            'lecture_hours' => fake()->numberBetween(1, 2),
            'practical_hours' => fake()->numberBetween(0, 3),
            'credit_units' => fake()->numberBetween(2, 4),
            'contact_hours' => fake()->numberBetween(2, 4),
            'year' => fake()->numberBetween(1, 2),
            'semester' => fake()->numberBetween(1, 2),
        ];
    }
}
