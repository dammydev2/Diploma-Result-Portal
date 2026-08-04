<?php

namespace Tests\Feature;

use App\Enums\ProgrammeLevel;
use App\Models\CourseUnit;
use App\Models\Programme;
use App\Models\Score;
use App\Models\Student;
use Database\Seeders\GradingScaleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_belongs_to_a_programme(): void
    {
        $programme = Programme::factory()->create();

        $student = Student::factory()->create(['programme_id' => $programme->id]);

        $this->assertTrue($student->programme->is($programme));
        $this->assertTrue($programme->students->contains($student));
    }

    public function test_a_student_is_not_suspended_by_default(): void
    {
        $student = Student::factory()->create();

        $this->assertFalse($student->isSuspended);
        $this->assertNull($student->suspended_at);
    }

    public function test_a_student_can_be_suspended_and_reactivated(): void
    {
        $student = Student::factory()->create();

        $student->suspend();

        $this->assertTrue($student->fresh()->isSuspended);
        $this->assertNotNull($student->fresh()->suspended_at);

        $student->reactivate();

        $this->assertFalse($student->fresh()->isSuspended);
        $this->assertNull($student->fresh()->suspended_at);
    }

    public function test_matric_no_must_be_unique(): void
    {
        Student::factory()->create(['matric_no' => '2024/0001']);

        $this->expectException(QueryException::class);

        Student::factory()->create(['matric_no' => '2024/0001']);
    }

    public function test_a_programme_cannot_be_deleted_while_it_has_students(): void
    {
        $programme = Programme::factory()->create();
        Student::factory()->create(['programme_id' => $programme->id]);

        $this->expectException(QueryException::class);

        $programme->delete();
    }

    public function test_the_students_level_comes_from_its_programme(): void
    {
        $programme = Programme::factory()->create(['level' => ProgrammeLevel::HigherNationalDiploma]);
        $student = Student::factory()->create(['programme_id' => $programme->id]);

        $this->assertSame(ProgrammeLevel::HigherNationalDiploma, $student->programme->level);
    }

    public function test_gpa_for_a_semester_is_weighted_by_credit_units(): void
    {
        $this->seed(GradingScaleSeeder::class);

        $programme = Programme::factory()->create();
        $student = Student::factory()->create(['programme_id' => $programme->id]);

        $courseA = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 3, 'year' => 1, 'semester' => 1]);
        $courseB = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 2, 'year' => 1, 'semester' => 1]);

        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseA->id, 'score' => 78]); // A, 4.00
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseB->id, 'score' => 42]); // E, 2.00

        // (4.00 * 3 + 2.00 * 2) / (3 + 2) = 16 / 5 = 3.2
        $this->assertSame(3.2, $student->gpaFor(1, 1));
    }

    public function test_gpa_for_a_semester_with_no_scores_is_null(): void
    {
        $student = Student::factory()->create();

        $this->assertNull($student->gpaFor(1, 1));
    }

    public function test_cgpa_covers_every_recorded_score_across_semesters(): void
    {
        $this->seed(GradingScaleSeeder::class);

        $programme = Programme::factory()->create();
        $student = Student::factory()->create(['programme_id' => $programme->id]);

        $courseA = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 3, 'year' => 1, 'semester' => 1]);
        $courseB = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 2, 'year' => 1, 'semester' => 1]);
        $courseC = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 4, 'year' => 1, 'semester' => 2]);

        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseA->id, 'score' => 78]); // A, 4.00
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseB->id, 'score' => 42]); // E, 2.00
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseC->id, 'score' => 55]); // C, 2.75

        // (4.00*3 + 2.00*2 + 2.75*4) / (3+2+4) = 27 / 9 = 3.0
        $this->assertSame(3.0, $student->cgpa());

        // gpaFor should only include that semester's courses.
        $this->assertSame(3.2, $student->gpaFor(1, 1));
    }

    public function test_cgpa_with_no_scores_is_null(): void
    {
        $student = Student::factory()->create();

        $this->assertNull($student->cgpa());
    }
}
