<?php

namespace Database\Factories;

use App\Models\CourseUnit;
use App\Models\Score;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Score>
 */
class ScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Note: the grading_scales table must be seeded before using this
     * factory's `create()`, since Score derives its letter_grade/weighting
     * from a GradingScale band on save.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'course_unit_id' => CourseUnit::factory(),
            'score' => fake()->numberBetween(40, 100),
        ];
    }
}
