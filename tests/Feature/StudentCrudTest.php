<?php

namespace Tests\Feature;

use App\Enums\ProgrammeLevel;
use App\Models\Programme;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('student.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_students_list(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('student.index'));

        $response->assertOk();
    }

    public function test_the_create_form_redirects_with_an_error_when_no_programme_exists(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('student.create'));

        $response->assertRedirect(route('student.index'));
        $response->assertSessionHas('alert_messages');
    }

    public function test_storing_is_blocked_with_an_error_when_no_programme_exists(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('student.store'), [
            'name' => 'Jane Doe',
            'matric_no' => '2024/0001',
            'year_of_admission' => 2024,
        ]);

        $response->assertRedirect(route('student.index'));

        $this->assertDatabaseMissing('students', ['matric_no' => '2024/0001']);
    }

    public function test_a_student_can_be_created(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        $response = $this->post(route('student.store'), [
            'programme_id' => $programme->id,
            'name' => 'Jane Doe',
            'matric_no' => '2024/0001',
            'year_of_admission' => 2024,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('students', [
            'programme_id' => $programme->id,
            'name' => 'Jane Doe',
            'matric_no' => '2024/0001',
            'year_of_admission' => 2024,
        ]);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs(User::factory()->create());

        Programme::factory()->create();

        $response = $this->post(route('student.store'), []);

        $response->assertSessionHasErrors([
            'programme_id', 'name', 'matric_no', 'year_of_admission',
        ]);
    }

    public function test_matric_no_must_be_unique(): void
    {
        $this->actingAs(User::factory()->create());

        $existing = Student::factory()->create();

        $response = $this->post(route('student.store'), [
            'programme_id' => $existing->programme_id,
            'name' => 'A different student',
            'matric_no' => $existing->matric_no,
            'year_of_admission' => $existing->year_of_admission,
        ]);

        $response->assertSessionHasErrors(['matric_no']);
    }

    public function test_a_student_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create());

        $student = Student::factory()->create();

        $response = $this->put(route('student.update', $student->id), [
            'id' => $student->id,
            'programme_id' => $student->programme_id,
            'name' => 'Updated name',
            'matric_no' => $student->matric_no,
            'year_of_admission' => $student->year_of_admission,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Updated name',
        ]);
    }

    public function test_there_is_no_delete_route(): void
    {
        $this->actingAs(User::factory()->create());

        $student = Student::factory()->create();

        $response = $this->delete(route('student.index').'/'.$student->id);

        $response->assertMethodNotAllowed();

        $this->assertModelExists($student);
    }

    public function test_the_create_form_disambiguates_same_named_programmes_by_level(): void
    {
        $this->actingAs(User::factory()->create());

        Programme::factory()->create(['name' => 'Computer Science', 'level' => ProgrammeLevel::NationalDiploma]);
        Programme::factory()->create(['name' => 'Computer Science', 'level' => ProgrammeLevel::HigherNationalDiploma]);

        $response = $this->get(route('student.create'));

        $response->assertOk();
        $response->assertSee('Computer Science (ND)');
        $response->assertSee('Computer Science (HND)');
    }

    public function test_a_student_can_be_suspended_and_reactivated_from_the_list(): void
    {
        $this->actingAs(User::factory()->create());

        $student = Student::factory()->create();

        $this->put(route('student.suspend', $student->id))->assertRedirect();
        $this->assertTrue($student->fresh()->isSuspended);

        $this->put(route('student.reactivate', $student->id))->assertRedirect();
        $this->assertFalse($student->fresh()->isSuspended);
    }
}
