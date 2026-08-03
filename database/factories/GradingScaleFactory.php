<?php

namespace Database\Factories;

use App\Models\GradingScale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradingScale>
 */
class GradingScaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'min_score' => 40,
            'max_score' => 44,
            'letter_grade' => fake()->unique()->lexify('?'),
            'weighting' => 2.0,
        ];
    }
}
