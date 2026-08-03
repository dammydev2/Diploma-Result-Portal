<?php

namespace Tests\Feature;

use App\Enums\ProgrammeLevel;
use App\Models\Programme;
use App\Models\Student;
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
}
