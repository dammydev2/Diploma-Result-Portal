<?php

namespace Tests\Feature;

use App\Models\Programme;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('page.students.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_students_page(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.students.index'));

        $response->assertOk();
    }

    public function test_students_are_listed_for_the_selected_programme_and_year_of_admission(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        $matching = Student::factory()->create([
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
        ]);

        $otherYear = Student::factory()->create([
            'programme_id' => $programme->id,
            'year_of_admission' => 2023,
        ]);

        $response = $this->get(route('page.students.index', [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
        ]));

        $response->assertOk();
        $response->assertSee($matching->matric_no);
        $response->assertDontSee($otherYear->matric_no);
    }

    public function test_suspended_students_are_labelled_as_such(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        $student = Student::factory()->suspended()->create([
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
        ]);

        $response = $this->get(route('page.students.index', [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
        ]));

        $response->assertOk();
        $response->assertSeeInOrder([$student->matric_no, 'Suspended']);
    }

    public function test_the_total_students_are_shown_for_the_selected_filters(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        Student::factory()->count(3)->create([
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
        ]);

        $response = $this->get(route('page.students.index', [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
        ]));

        $response->assertOk();
        $response->assertSeeInOrder(['TOTAL STUDENTS', '3']);
    }

    public function test_no_students_are_shown_until_a_full_selection_is_made(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        Student::factory()->create(['programme_id' => $programme->id]);

        $response = $this->get(route('page.students.index', ['programme_id' => $programme->id]));

        $response->assertOk();
        $response->assertSee('Select a programme and year of admission to view students.');
    }
}
