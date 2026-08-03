<?php

namespace Tests\Feature;

use App\Models\GradingScale;
use Database\Seeders\GradingScaleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingScaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_seeder_creates_the_nine_grade_bands(): void
    {
        $this->seed(GradingScaleSeeder::class);

        $this->assertSame(9, GradingScale::count());

        $this->assertDatabaseHas('grading_scales', ['letter_grade' => 'A', 'min_score' => 75, 'max_score' => 100, 'weighting' => 4.00]);
        $this->assertDatabaseHas('grading_scales', ['letter_grade' => 'AB', 'min_score' => 70, 'max_score' => 74, 'weighting' => 3.50]);
        $this->assertDatabaseHas('grading_scales', ['letter_grade' => 'B', 'min_score' => 65, 'max_score' => 69, 'weighting' => 3.25]);
        $this->assertDatabaseHas('grading_scales', ['letter_grade' => 'BC', 'min_score' => 60, 'max_score' => 64, 'weighting' => 3.00]);
        $this->assertDatabaseHas('grading_scales', ['letter_grade' => 'C', 'min_score' => 55, 'max_score' => 59, 'weighting' => 2.75]);
        $this->assertDatabaseHas('grading_scales', ['letter_grade' => 'CD', 'min_score' => 50, 'max_score' => 54, 'weighting' => 2.50]);
        $this->assertDatabaseHas('grading_scales', ['letter_grade' => 'D', 'min_score' => 45, 'max_score' => 49, 'weighting' => 2.25]);
        $this->assertDatabaseHas('grading_scales', ['letter_grade' => 'E', 'min_score' => 40, 'max_score' => 44, 'weighting' => 2.00]);
        $this->assertDatabaseHas('grading_scales', ['letter_grade' => 'F', 'min_score' => 0, 'max_score' => 39, 'weighting' => 0.00]);
    }

    public function test_the_seeder_is_idempotent(): void
    {
        $this->seed(GradingScaleSeeder::class);
        $this->seed(GradingScaleSeeder::class);

        $this->assertSame(9, GradingScale::count());
    }

    public function test_for_score_finds_the_matching_band(): void
    {
        $this->seed(GradingScaleSeeder::class);

        $this->assertSame('A', GradingScale::forScore(80)->letter_grade);
        $this->assertSame('A', GradingScale::forScore(75)->letter_grade);
        $this->assertSame('AB', GradingScale::forScore(74)->letter_grade);
        $this->assertSame('E', GradingScale::forScore(40)->letter_grade);
        $this->assertSame('F', GradingScale::forScore(39)->letter_grade);
        $this->assertSame('F', GradingScale::forScore(0)->letter_grade);
    }

    public function test_for_score_returns_null_outside_any_band(): void
    {
        $this->seed(GradingScaleSeeder::class);

        $this->assertNull(GradingScale::forScore(101));
    }

    public function test_letter_grade_must_be_unique(): void
    {
        GradingScale::factory()->create(['letter_grade' => 'A']);

        $this->expectException(QueryException::class);

        GradingScale::factory()->create(['letter_grade' => 'A']);
    }
}
