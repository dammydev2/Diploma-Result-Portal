<?php

namespace Tests\Feature;

use App\Models\CourseUnit;
use App\Models\Programme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_course_unit_belongs_to_a_programme(): void
    {
        $programme = Programme::factory()->create();

        $courseUnit = CourseUnit::factory()->create(['programme_id' => $programme->id]);

        $this->assertTrue($courseUnit->programme->is($programme));
        $this->assertTrue($programme->courseUnits->contains($courseUnit));
    }

    public function test_a_course_unit_can_have_a_prerequisite(): void
    {
        $prerequisite = CourseUnit::factory()->create();

        $courseUnit = CourseUnit::factory()->create([
            'prerequisite_course_unit_id' => $prerequisite->id,
        ]);

        $this->assertTrue($courseUnit->prerequisite->is($prerequisite));
    }

    public function test_a_course_unit_can_have_no_prerequisite(): void
    {
        $courseUnit = CourseUnit::factory()->create(['prerequisite_course_unit_id' => null]);

        $this->assertNull($courseUnit->prerequisite);
    }

    public function test_deleting_a_programme_deletes_its_course_units(): void
    {
        $programme = Programme::factory()->create();
        $courseUnit = CourseUnit::factory()->create(['programme_id' => $programme->id]);

        $programme->delete();

        $this->assertDatabaseMissing('course_units', ['id' => $courseUnit->id]);
    }
}
