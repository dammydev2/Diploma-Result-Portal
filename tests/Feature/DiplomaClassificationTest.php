<?php

namespace Tests\Feature;

use App\Models\DiplomaClassification;
use Database\Seeders\DiplomaClassificationSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiplomaClassificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_seeder_creates_the_five_classification_bands(): void
    {
        $this->seed(DiplomaClassificationSeeder::class);

        $this->assertSame(5, DiplomaClassification::count());

        $this->assertDatabaseHas('diploma_classifications', ['classification' => 'Distinction', 'min_cgpa' => 3.50, 'max_cgpa' => 4.00]);
        $this->assertDatabaseHas('diploma_classifications', ['classification' => 'Upper Credit', 'min_cgpa' => 3.00, 'max_cgpa' => 3.49]);
        $this->assertDatabaseHas('diploma_classifications', ['classification' => 'Lower Credit', 'min_cgpa' => 2.50, 'max_cgpa' => 2.99]);
        $this->assertDatabaseHas('diploma_classifications', ['classification' => 'Pass', 'min_cgpa' => 2.00, 'max_cgpa' => 2.49]);
        $this->assertDatabaseHas('diploma_classifications', ['classification' => 'Fail', 'min_cgpa' => 0.00, 'max_cgpa' => 1.99]);
    }

    public function test_the_seeder_is_idempotent(): void
    {
        $this->seed(DiplomaClassificationSeeder::class);
        $this->seed(DiplomaClassificationSeeder::class);

        $this->assertSame(5, DiplomaClassification::count());
    }

    public function test_for_cgpa_finds_the_matching_band(): void
    {
        $this->seed(DiplomaClassificationSeeder::class);

        $this->assertSame('Distinction', DiplomaClassification::forCgpa(4.00)->classification);
        $this->assertSame('Distinction', DiplomaClassification::forCgpa(3.50)->classification);
        $this->assertSame('Upper Credit', DiplomaClassification::forCgpa(3.49)->classification);
        $this->assertSame('Pass', DiplomaClassification::forCgpa(2.00)->classification);
        $this->assertSame('Fail', DiplomaClassification::forCgpa(1.99)->classification);
        $this->assertSame('Fail', DiplomaClassification::forCgpa(0.00)->classification);
    }

    public function test_for_cgpa_returns_null_outside_any_band(): void
    {
        $this->seed(DiplomaClassificationSeeder::class);

        $this->assertNull(DiplomaClassification::forCgpa(4.01));
    }

    public function test_classification_must_be_unique(): void
    {
        DiplomaClassification::factory()->create(['classification' => 'Pass']);

        $this->expectException(QueryException::class);

        DiplomaClassification::factory()->create(['classification' => 'Pass']);
    }
}
