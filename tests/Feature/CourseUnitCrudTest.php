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

    public function test_the_create_form_redirects_with_an_error_when_no_programme_exists(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('course-unit.create'));

        $response->assertRedirect(route('course-unit.index'));
        $response->assertSessionHas('alert_messages');
    }

    public function test_storing_is_blocked_with_an_error_when_no_programme_exists(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('course-unit.store'), [
            'code' => 'COM 111',
            'title' => 'Introduction to Computing',
            'credit_units' => 3,
            'year' => 1,
            'semester' => 1,
        ]);

        $response->assertRedirect(route('course-unit.index'));

        $this->assertDatabaseMissing('course_units', ['code' => 'COM 111']);
    }

    public function test_a_course_unit_can_be_created(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        $response = $this->post(route('course-unit.store'), [
            'programme_id' => $programme->id,
            'code' => 'COM 111',
            'title' => 'Introduction to Computing',
            'credit_units' => 3,
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

        Programme::factory()->create();

        $response = $this->post(route('course-unit.store'), []);

        $response->assertSessionHasErrors([
            'programme_id', 'code', 'title', 'credit_units', 'year', 'semester',
        ]);
    }

    public function test_course_unit_code_must_be_unique_within_the_same_programme(): void
    {
        $this->actingAs(User::factory()->create());

        $existing = CourseUnit::factory()->create();

        $response = $this->post(route('course-unit.store'), [
            'programme_id' => $existing->programme_id,
            'code' => $existing->code,
            'title' => 'A different title',
            'credit_units' => 3,
            'year' => 1,
            'semester' => 1,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_course_unit_code_can_repeat_across_different_programmes(): void
    {
        $this->actingAs(User::factory()->create());

        $existing = CourseUnit::factory()->create(['code' => 'COM 111']);
        $otherProgramme = Programme::factory()->create();

        $response = $this->post(route('course-unit.store'), [
            'programme_id' => $otherProgramme->id,
            'code' => 'COM 111',
            'title' => 'Introduction to Computing',
            'credit_units' => 3,
            'year' => 1,
            'semester' => 1,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('course_units', [
            'programme_id' => $existing->programme_id,
            'code' => 'COM 111',
        ]);
        $this->assertDatabaseHas('course_units', [
            'programme_id' => $otherProgramme->id,
            'code' => 'COM 111',
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
            'credit_units' => $courseUnit->credit_units,
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
