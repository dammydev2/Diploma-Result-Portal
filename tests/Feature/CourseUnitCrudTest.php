<?php

namespace Tests\Feature;

use App\Models\CourseUnit;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseUnitCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('course-unit.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_course_units_list(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('course-unit.index'));

        $response->assertOk();
    }

    public function test_a_course_unit_can_be_created(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        $response = $this->post(route('course-unit.store'), [
            'programme_id' => $programme->id,
            'code' => 'COM 111',
            'title' => 'Introduction to Computing',
            'lecture_hours' => 2,
            'practical_hours' => 2,
            'credit_units' => 3,
            'contact_hours' => 4,
            'year' => 1,
            'semester' => 1,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('course_units', [
            'programme_id' => $programme->id,
            'code' => 'COM 111',
            'title' => 'Introduction to Computing',
        ]);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('course-unit.store'), []);

        $response->assertSessionHasErrors([
            'programme_id', 'code', 'title', 'lecture_hours', 'practical_hours', 'credit_units', 'contact_hours', 'year', 'semester',
        ]);
    }

    public function test_course_unit_code_must_be_unique(): void
    {
        $this->actingAs(User::factory()->create());

        $existing = CourseUnit::factory()->create();

        $response = $this->post(route('course-unit.store'), [
            'programme_id' => $existing->programme_id,
            'code' => $existing->code,
            'title' => 'A different title',
            'lecture_hours' => 2,
            'practical_hours' => 2,
            'credit_units' => 3,
            'contact_hours' => 4,
            'year' => 1,
            'semester' => 1,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_a_course_unit_can_have_a_prerequisite(): void
    {
        $this->actingAs(User::factory()->create());

        $prerequisite = CourseUnit::factory()->create();
        $programme = Programme::factory()->create();

        $response = $this->post(route('course-unit.store'), [
            'programme_id' => $programme->id,
            'prerequisite_course_unit_id' => $prerequisite->id,
            'code' => 'COM 121',
            'title' => 'Programming using C Language',
            'lecture_hours' => 2,
            'practical_hours' => 2,
            'credit_units' => 3,
            'contact_hours' => 4,
            'year' => 1,
            'semester' => 2,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('course_units', [
            'code' => 'COM 121',
            'prerequisite_course_unit_id' => $prerequisite->id,
        ]);
    }

    public function test_a_course_unit_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create());

        $courseUnit = CourseUnit::factory()->create();

        $response = $this->put(route('course-unit.update', $courseUnit->id), [
            'id' => $courseUnit->id,
            'programme_id' => $courseUnit->programme_id,
            'code' => 'UPD 111',
            'title' => 'Updated title',
            'lecture_hours' => $courseUnit->lecture_hours,
            'practical_hours' => $courseUnit->practical_hours,
            'credit_units' => $courseUnit->credit_units,
            'contact_hours' => $courseUnit->contact_hours,
            'year' => $courseUnit->year,
            'semester' => $courseUnit->semester,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('course_units', [
            'id' => $courseUnit->id,
            'code' => 'UPD 111',
            'title' => 'Updated title',
        ]);
    }

    public function test_a_course_unit_can_be_deleted(): void
    {
        $this->actingAs(User::factory()->create());

        $courseUnit = CourseUnit::factory()->create();

        $response = $this->delete(route('course-unit.destroy', $courseUnit->id));

        $response->assertOk();

        $this->assertDatabaseMissing('course_units', ['id' => $courseUnit->id]);
    }
}
