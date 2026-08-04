<?php

namespace Tests\Feature;

use App\Models\CourseUnit;
use App\Models\Programme;
use App\Models\Score;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\GradingScaleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoresPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('page.scores.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_scores_page(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.scores.index'));

        $response->assertOk();
    }

    public function test_the_mark_sheet_lists_the_cohorts_students(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();
        $courseUnit = CourseUnit::factory()->create(['programme_id' => $programme->id]);
        $student = Student::factory()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);

        $response = $this->get(route('page.scores.index', [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
            'course_unit_id' => $courseUnit->id,
        ]));

        $response->assertOk();
        $response->assertSee($student->matric_no);
    }

    public function test_suspended_students_have_no_score_input(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();
        $courseUnit = CourseUnit::factory()->create(['programme_id' => $programme->id]);
        $student = Student::factory()->suspended()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);

        $response = $this->get(route('page.scores.index', [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
            'course_unit_id' => $courseUnit->id,
        ]));

        $response->assertOk();
        $response->assertSee($student->matric_no);
        $response->assertDontSee("scores[{$student->id}]", false);
    }

    public function test_submitting_scores_creates_them_with_derived_grades(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        $programme = Programme::factory()->create();
        $courseUnit = CourseUnit::factory()->create(['programme_id' => $programme->id]);
        $student = Student::factory()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);

        $response = $this->post(route('page.scores.store'), [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
            'course_unit_id' => $courseUnit->id,
            'scores' => [$student->id => 78],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('scores', [
            'student_id' => $student->id,
            'course_unit_id' => $courseUnit->id,
            'score' => 78,
            'letter_grade' => 'A',
            'weighting' => 4.00,
        ]);
    }

    public function test_submitting_updates_an_existing_score(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        $programme = Programme::factory()->create();
        $courseUnit = CourseUnit::factory()->create(['programme_id' => $programme->id]);
        $student = Student::factory()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);

        $score = Score::factory()->create([
            'student_id' => $student->id,
            'course_unit_id' => $courseUnit->id,
            'score' => 42,
        ]);

        $this->post(route('page.scores.store'), [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
            'course_unit_id' => $courseUnit->id,
            'scores' => [$student->id => 90],
        ]);

        $this->assertSame(90, $score->fresh()->score);
        $this->assertSame('A', $score->fresh()->letter_grade);
    }

    public function test_blank_scores_are_skipped(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        $programme = Programme::factory()->create();
        $courseUnit = CourseUnit::factory()->create(['programme_id' => $programme->id]);
        $student = Student::factory()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);

        $this->post(route('page.scores.store'), [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
            'course_unit_id' => $courseUnit->id,
            'scores' => [$student->id => ''],
        ]);

        $this->assertDatabaseMissing('scores', ['student_id' => $student->id]);
    }

    public function test_suspended_students_scores_are_never_saved_even_if_submitted(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        $programme = Programme::factory()->create();
        $courseUnit = CourseUnit::factory()->create(['programme_id' => $programme->id]);
        $student = Student::factory()->suspended()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);

        $this->post(route('page.scores.store'), [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
            'course_unit_id' => $courseUnit->id,
            'scores' => [$student->id => 80],
        ]);

        $this->assertDatabaseMissing('scores', ['student_id' => $student->id]);
    }

    public function test_score_must_be_within_zero_to_a_hundred(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();
        $courseUnit = CourseUnit::factory()->create(['programme_id' => $programme->id]);
        $student = Student::factory()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);

        $response = $this->post(route('page.scores.store'), [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
            'course_unit_id' => $courseUnit->id,
            'scores' => [$student->id => 150],
        ]);

        $response->assertSessionHasErrors(["scores.{$student->id}"]);
    }
}
