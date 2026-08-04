<?php

namespace Tests\Feature;

use App\Models\CourseUnit;
use App\Models\GradingScale;
use App\Models\Score;
use App\Models\Student;
use Database\Seeders\GradingScaleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ScoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GradingScaleSeeder::class);
    }

    public function test_a_score_belongs_to_a_student_and_course_unit(): void
    {
        $student = Student::factory()->create();
        $courseUnit = CourseUnit::factory()->create();

        $score = Score::factory()->create([
            'student_id' => $student->id,
            'course_unit_id' => $courseUnit->id,
        ]);

        $this->assertTrue($score->student->is($student));
        $this->assertTrue($score->courseUnit->is($courseUnit));
        $this->assertTrue($student->scores->contains($score));
        $this->assertTrue($courseUnit->scores->contains($score));
    }

    public function test_letter_grade_and_weighting_are_derived_from_the_grading_scale_on_create(): void
    {
        $score = Score::factory()->create(['score' => 78]);

        $this->assertSame('A', $score->letter_grade);
        $this->assertSame('4.00', (string) $score->weighting);
    }

    public function test_letter_grade_and_weighting_are_re_derived_when_the_score_changes(): void
    {
        $score = Score::factory()->create(['score' => 78]);

        $score->update(['score' => 42]);

        $this->assertSame('E', $score->letter_grade);
        $this->assertSame('2.00', (string) $score->weighting);
    }

    public function test_a_score_cannot_be_recorded_for_a_suspended_student(): void
    {
        $student = Student::factory()->suspended()->create();

        $this->expectException(RuntimeException::class);

        Score::factory()->create(['student_id' => $student->id]);
    }

    public function test_a_student_can_only_have_one_score_per_course_unit(): void
    {
        $student = Student::factory()->create();
        $courseUnit = CourseUnit::factory()->create();

        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseUnit->id]);

        $this->expectException(QueryException::class);

        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseUnit->id]);
    }

    public function test_saving_fails_when_no_grading_scale_band_covers_the_score(): void
    {
        GradingScale::query()->delete();

        $this->expectException(RuntimeException::class);

        Score::factory()->create(['score' => 60]);
    }
}
