<?php

namespace Database\Factories;

use App\Models\Programme;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $yearOfAdmission = fake()->numberBetween(2018, (int) now()->format('Y'));

        return [
            'programme_id' => Programme::factory(),
            'name' => fake()->name(),
            'matric_no' => $yearOfAdmission.'/'.fake()->unique()->numerify('####'),
            'year_of_admission' => $yearOfAdmission,
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => ['suspended_at' => now()]);
    }
}
