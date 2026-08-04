<?php

namespace Tests\Feature;

use App\Models\CourseUnit;
use App\Models\Programme;
use App\Models\Score;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\GradingScaleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultsPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeFilters(): array
    {
        $programme = Programme::factory()->create();

        $courseA = CourseUnit::factory()->create([
            'programme_id' => $programme->id,
            'credit_units' => 3,
            'year' => 1,
            'semester' => 1,
        ]);

        $courseB = CourseUnit::factory()->create([
            'programme_id' => $programme->id,
            'credit_units' => 4,
            'year' => 1,
            'semester' => 1,
        ]);

        $studentComplete = Student::factory()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);
        $studentIncomplete = Student::factory()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);

        return compact('programme', 'courseA', 'courseB', 'studentComplete', 'studentIncomplete');
    }

    private function filterParams(Programme $programme): array
    {
        return [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
            'year' => 1,
            'semester' => 1,
        ];
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('page.results.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_results_page(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.results.index'));

        $response->assertOk();
    }

    public function test_no_result_is_shown_until_at_least_one_student_is_complete(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        ['programme' => $programme, 'courseA' => $courseA, 'studentIncomplete' => $student] = $this->makeFilters();

        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseA->id, 'score' => 78]);

        $response = $this->get(route('page.results.index', $this->filterParams($programme)));

        $response->assertOk();
        $response->assertSee('No complete result yet for this semester');
    }

    public function test_not_ready_state_names_courses_with_no_scores_entered_at_all(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        [
            'programme' => $programme,
            'courseA' => $courseA,
            'courseB' => $courseB,
            'studentIncomplete' => $student,
        ] = $this->makeFilters();

        // courseA has a score for someone; courseB has none at all yet.
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseA->id, 'score' => 78]);

        $response = $this->get(route('page.results.index', $this->filterParams($programme)));

        $response->assertOk();
        $response->assertSee('No scores have been entered yet for');
        $response->assertSee($courseB->code);
        $response->assertDontSee('No scores have been entered yet for: '.$courseA->code);
    }

    public function test_the_result_shows_once_at_least_one_student_is_complete(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        [
            'programme' => $programme,
            'courseA' => $courseA,
            'courseB' => $courseB,
            'studentComplete' => $complete,
            'studentIncomplete' => $incomplete,
        ] = $this->makeFilters();

        // Complete student: both courses scored.
        Score::factory()->create(['student_id' => $complete->id, 'course_unit_id' => $courseA->id, 'score' => 78]); // A, 4.00
        Score::factory()->create(['student_id' => $complete->id, 'course_unit_id' => $courseB->id, 'score' => 42]); // E, 2.00

        // Incomplete student: only one course scored, the other left blank.
        Score::factory()->create(['student_id' => $incomplete->id, 'course_unit_id' => $courseA->id, 'score' => 78]);

        $response = $this->get(route('page.results.index', $this->filterParams($programme)));

        $response->assertOk();
        $response->assertDontSee('No complete result yet for this semester');
        $response->assertSee($complete->matric_no);
        $response->assertSee($incomplete->matric_no);
        $response->assertSee($courseA->code);
        $response->assertSee($courseB->code);

        // Complete student's GP: (4.00*3 + 2.00*4) / (3+4) = 20/7 = 2.86
        $response->assertSee('2.86');

        // Incomplete student's GP only counts the one scored course: (4.00*3)/3 = 4
        $response->assertSee('4');

        // Footnote about carry-over is present.
        $response->assertSee('automatic carry-over');

        // Programme + level + year line in the header.
        $response->assertSee(strtoupper($programme->name).' '.$programme->level->value.' 1');

        // Year 1 Semester 1 has no prior semester, so no GPA/CGPA columns.
        $response->assertDontSee('>GPA<', false);
        $response->assertDontSee('>CGPA<', false);
    }

    public function test_gpa_and_cgpa_columns_appear_from_the_second_semester_onward(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        $programme = Programme::factory()->create();

        $courseA = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 3, 'year' => 1, 'semester' => 1]);
        $courseB = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 4, 'year' => 1, 'semester' => 1]);
        $courseC = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 5, 'year' => 1, 'semester' => 2]);

        $student = Student::factory()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);

        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseA->id, 'score' => 78]); // A, 4.00
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseB->id, 'score' => 42]); // E, 2.00
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseC->id, 'score' => 66]); // B, 3.25

        $response = $this->get(route('page.results.index', [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
            'year' => 1,
            'semester' => 2,
        ]));

        $response->assertOk();
        $response->assertDontSee('No complete result yet for this semester');
        $response->assertSee('>GPA<', false);
        $response->assertSee('>CGPA<', false);

        // Current semester GP: just courseC, weighting 3.25.
        $response->assertSee('3.25');

        // GPA (through year 1 semester 1 only): (4.00*3 + 2.00*4) / 7 = 2.86
        $response->assertSee('2.86');

        // CGPA (through year 1 semester 2 inclusive): (12 + 8 + 3.25*5) / 12 = 3.02
        $response->assertSee('3.02');
    }

    public function test_the_result_page_reflects_a_saved_institution_setting(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);
        Setting::set('institution_name', 'Dots ICT Institute of Technology');
        Setting::set('institution_address', '12 Example Street, Lagos');

        [
            'programme' => $programme,
            'courseA' => $courseA,
            'courseB' => $courseB,
            'studentComplete' => $complete,
        ] = $this->makeFilters();

        Score::factory()->create(['student_id' => $complete->id, 'course_unit_id' => $courseA->id, 'score' => 78]);
        Score::factory()->create(['student_id' => $complete->id, 'course_unit_id' => $courseB->id, 'score' => 42]);

        $response = $this->get(route('page.results.index', $this->filterParams($programme)));

        $response->assertOk();
        $response->assertSee('Dots ICT Institute of Technology');
        $response->assertSee('12 Example Street, Lagos');
    }

    public function test_export_routes_404_when_no_student_is_complete(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        ['programme' => $programme] = $this->makeFilters();

        $this->get(route('page.results.export.pdf', $this->filterParams($programme)))->assertNotFound();
        $this->get(route('page.results.export.excel', $this->filterParams($programme)))->assertNotFound();
    }

    public function test_pdf_export_downloads_once_ready(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        [
            'programme' => $programme,
            'courseA' => $courseA,
            'courseB' => $courseB,
            'studentComplete' => $complete,
        ] = $this->makeFilters();

        Score::factory()->create(['student_id' => $complete->id, 'course_unit_id' => $courseA->id, 'score' => 78]);
        Score::factory()->create(['student_id' => $complete->id, 'course_unit_id' => $courseB->id, 'score' => 42]);

        $response = $this->get(route('page.results.export.pdf', $this->filterParams($programme)));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_excel_export_downloads_once_ready(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        [
            'programme' => $programme,
            'courseA' => $courseA,
            'courseB' => $courseB,
            'studentComplete' => $complete,
        ] = $this->makeFilters();

        Score::factory()->create(['student_id' => $complete->id, 'course_unit_id' => $courseA->id, 'score' => 78]);
        Score::factory()->create(['student_id' => $complete->id, 'course_unit_id' => $courseB->id, 'score' => 42]);

        $response = $this->get(route('page.results.export.excel', $this->filterParams($programme)));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }
}
