<?php

namespace Database\Seeders;

use App\Models\GradingScale;
use Illuminate\Database\Seeder;

class GradingScaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bands = [
            ['min_score' => 75, 'max_score' => 100, 'letter_grade' => 'A', 'weighting' => 4.00],
            ['min_score' => 70, 'max_score' => 74, 'letter_grade' => 'AB', 'weighting' => 3.50],
            ['min_score' => 65, 'max_score' => 69, 'letter_grade' => 'B', 'weighting' => 3.25],
            ['min_score' => 60, 'max_score' => 64, 'letter_grade' => 'BC', 'weighting' => 3.00],
            ['min_score' => 55, 'max_score' => 59, 'letter_grade' => 'C', 'weighting' => 2.75],
            ['min_score' => 50, 'max_score' => 54, 'letter_grade' => 'CD', 'weighting' => 2.50],
            ['min_score' => 45, 'max_score' => 49, 'letter_grade' => 'D', 'weighting' => 2.25],
            ['min_score' => 40, 'max_score' => 44, 'letter_grade' => 'E', 'weighting' => 2.00],
            ['min_score' => 0, 'max_score' => 39, 'letter_grade' => 'F', 'weighting' => 0.00],
        ];

        foreach ($bands as $band) {
            GradingScale::query()->updateOrCreate(
                ['letter_grade' => $band['letter_grade']],
                $band
            );
        }
    }
}
