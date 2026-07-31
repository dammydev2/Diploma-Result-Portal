<?php

namespace Database\Factories;

use App\Enums\ProgrammeLevel;
use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Programme>
 */
class ProgrammeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = fake()->randomElement(ProgrammeLevel::cases());

        return [
            'name' => ucwords(fake()->unique()->word().' '.fake()->unique()->word()),
            'code' => strtoupper(fake()->unique()->lexify('???')).'-'.$level->value,
            'level' => $level,
            'min_credit_units' => 72,
            'max_credit_units' => 80,
        ];
    }

    public function nationalDiploma(): static
    {
        return $this->state(fn (): array => ['level' => ProgrammeLevel::NationalDiploma]);
    }

    public function higherNationalDiploma(): static
    {
        return $this->state(fn (): array => ['level' => ProgrammeLevel::HigherNationalDiploma]);
    }
}
