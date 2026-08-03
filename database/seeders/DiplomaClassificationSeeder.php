<?php

namespace Database\Seeders;

use App\Models\DiplomaClassification;
use Illuminate\Database\Seeder;

class DiplomaClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bands = [
            ['min_cgpa' => 3.50, 'max_cgpa' => 4.00, 'classification' => 'Distinction'],
            ['min_cgpa' => 3.00, 'max_cgpa' => 3.49, 'classification' => 'Upper Credit'],
            ['min_cgpa' => 2.50, 'max_cgpa' => 2.99, 'classification' => 'Lower Credit'],
            ['min_cgpa' => 2.00, 'max_cgpa' => 2.49, 'classification' => 'Pass'],
            ['min_cgpa' => 0.00, 'max_cgpa' => 1.99, 'classification' => 'Fail'],
        ];

        foreach ($bands as $band) {
            DiplomaClassification::query()->updateOrCreate(
                ['classification' => $band['classification']],
                $band
            );
        }
    }
}
