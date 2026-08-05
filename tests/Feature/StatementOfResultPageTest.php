<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\StatementOfResultController;
use App\Models\CourseUnit;
use App\Models\Programme;
use App\Models\Score;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DiplomaClassificationSeeder;
use Database\Seeders\GradingScaleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class StatementOfResultPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{programme: Programme, courseA: CourseUnit, courseB: CourseUnit, courseC: CourseUnit, courseD: CourseUnit, complete: Student, incomplete: Student}
     */
    private function makeFixture(): array
    {
        $programme = Programme::factory()->create();

        $courseA = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 3, 'year' => 1, 'semester' => 1]);
        $courseB = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 4, 'year' => 1, 'semester' => 1]);
        $courseC = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 5, 'year' => 1, 'semester' => 2]);
        $courseD = CourseUnit::factory()->create(['programme_id' => $programme->id, 'credit_units' => 2, 'year' => 1, 'semester' => 2]);

        $complete = Student::factory()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);
        $incomplete = Student::factory()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);

        return compact('programme', 'courseA', 'courseB', 'courseC', 'courseD', 'complete', 'incomplete');
    }

    /**
     * @return array<string, mixed>
     */
    private function filterParams(Programme $programme, Student $student): array
    {
        return [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
            'student_id' => $student->id,
            'approval_date' => '2026-08-05',
        ];
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('page.statement-of-result.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_page(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.statement-of-result.index'));

        $response->assertOk();
    }

    public function test_no_statement_is_shown_until_the_student_has_a_complete_result(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        ['programme' => $programme, 'courseA' => $courseA, 'incomplete' => $student] = $this->makeFixture();

        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseA->id, 'score' => 78]);

        $response = $this->get(route('page.statement-of-result.index', $this->filterParams($programme, $student)));

        $response->assertOk();
        $response->assertSee('does not have a complete result yet');
    }

    public function test_the_statement_shows_once_the_student_has_a_complete_result(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);
        $this->seed(DiplomaClassificationSeeder::class);

        [
            'programme' => $programme,
            'courseA' => $courseA,
            'courseB' => $courseB,
            'courseC' => $courseC,
            'courseD' => $courseD,
            'complete' => $student,
        ] = $this->makeFixture();

        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseA->id, 'score' => 78]); // A, 4.00
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseB->id, 'score' => 42]); // E, 2.00
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseC->id, 'score' => 66]); // B, 3.25
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseD->id, 'score' => 30]); // F, 0.00

        $response = $this->get(route('page.statement-of-result.index', $this->filterParams($programme, $student)));

        $response->assertOk();
        $response->assertDontSee('does not have a complete result yet');
        $response->assertSee($student->matric_no);
        $response->assertSee($courseA->code);
        $response->assertSee($courseD->code);

        // Block 1 (Y1S1): GPA (4.00*3 + 2.00*4)/7 = 2.86, MAX UNIT 7, UNIT PASSED 7 (both pass)
        $response->assertSee('2.86');

        // Block 2 (Y1S2): GPA (3.25*5 + 0.00*2)/7 = 2.32, MAX UNIT 7, UNIT PASSED 5 (courseD is F)
        $response->assertSee('2.32');

        // Cumulative CGPA through both blocks: (12+8+16.25+0)/14 = 2.59
        $response->assertSee('2.59');

        // 2.59 falls in the "Lower Credit" band.
        $response->assertSee('LOWER CREDIT');

        // Registrar sign-off and alteration note are present.
        $response->assertSee('Registrar');
        $response->assertSee('ANY ALTERATION WHATSOEVER RENDERS THIS RESULT INVALID');
    }

    public function test_export_route_404s_when_the_student_is_not_complete(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        ['programme' => $programme, 'incomplete' => $student] = $this->makeFixture();

        $this->get(route('page.statement-of-result.export.pdf', $this->filterParams($programme, $student)))
            ->assertNotFound();
    }

    public function test_pdf_export_downloads_once_the_student_is_complete(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);
        $this->seed(DiplomaClassificationSeeder::class);

        [
            'programme' => $programme,
            'courseA' => $courseA,
            'courseB' => $courseB,
            'courseC' => $courseC,
            'courseD' => $courseD,
            'complete' => $student,
        ] = $this->makeFixture();

        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseA->id, 'score' => 78]);
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseB->id, 'score' => 42]);
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseC->id, 'score' => 66]);
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $courseD->id, 'score' => 30]);

        $response = $this->get(route('page.statement-of-result.export.pdf', $this->filterParams($programme, $student)));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_semester_blocks_are_padded_with_blank_rows_to_a_common_height(): void
    {
        $this->seed(GradingScaleSeeder::class);

        $programme = Programme::factory()->create();

        // Block 1: a single course. The course title column is wide enough
        // that titles always print on one line, so padding is driven purely
        // by course count, not title length.
        $singleCourse = CourseUnit::factory()->create([
            'programme_id' => $programme->id,
            'title' => 'A Considerably Longer Course Title That Still Fits On One Line',
            'year' => 1,
            'semester' => 1,
        ]);

        // Block 2: three courses, making it the tallest block on the page.
        $tripleCourses = CourseUnit::factory()->count(3)->create([
            'programme_id' => $programme->id,
            'year' => 1,
            'semester' => 2,
        ]);

        $student = Student::factory()->create(['programme_id' => $programme->id, 'year_of_admission' => 2024]);
        Score::factory()->create(['student_id' => $student->id, 'course_unit_id' => $singleCourse->id, 'score' => 78]);
        $tripleCourses->each(fn (CourseUnit $courseUnit) => Score::factory()->create([
            'student_id' => $student->id,
            'course_unit_id' => $courseUnit->id,
            'score' => 66,
        ]));

        $controller = new StatementOfResultController;
        $method = (new ReflectionClass($controller))->getMethod('buildSemesterBlocks');

        /** @var list<array{blankRowCount: int, courses: list<array{courseUnit: CourseUnit}>}> $blocks */
        $blocks = $method->invoke($controller, $student, 2024);

        $this->assertCount(2, $blocks);

        $singleBlock = collect($blocks)->firstWhere(fn (array $block): bool => count($block['courses']) === 1);
        $tripleBlock = collect($blocks)->firstWhere(fn (array $block): bool => count($block['courses']) === 3);

        // The block with the most courses is already the tallest, so it
        // needs no padding...
        $this->assertSame(0, $tripleBlock['blankRowCount']);

        // ...while the single-course block should be padded with two blank
        // rows to match its height, regardless of its title's length.
        $this->assertSame(2, $singleBlock['blankRowCount']);
    }
}
