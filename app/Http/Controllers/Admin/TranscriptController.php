<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\ResolvesInstitutionBranding;
use App\Models\CourseUnit;
use App\Models\DiplomaClassification;
use App\Models\Programme;
use App\Models\Score;
use App\Models\Student;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TranscriptController
 *
 * Same document as the Statement of Result — same data, same layout, same
 * GPA/CGPA/classification figures — except the per-course cell shows the
 * student's raw numeric score instead of its derived letter grade.
 *
 * @property-read CrudPanel $crud
 */
class TranscriptController extends Controller
{
    use ResolvesInstitutionBranding;

    private const string ALTERATION_NOTE = 'NOTE: ANY ALTERATION WHATSOEVER RENDERS THIS RESULT INVALID';

    public function index(Request $request): View
    {
        $result = $this->resolveResult($request);

        return view('admin.transcript', [
            'title' => 'Transcript',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Transcript' => false,
            ],
            'alterationNote' => self::ALTERATION_NOTE,
            ...$this->resolveBranding(),
            ...$result,
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $result = $this->resolveResult($request);
        $student = $result['selectedStudent'];

        abort_unless($result['isReady'] && $student !== null, 404);

        $pdf = Pdf::loadView('admin.transcript-pdf', [
            'alterationNote' => self::ALTERATION_NOTE,
            ...$this->resolveBranding(),
            ...$result,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($this->exportFilename($student).'.pdf');
    }

    /**
     * @return array{
     *     programmes: Collection<int, Programme>,
     *     selectedProgramme: ?Programme,
     *     programmeId: ?int,
     *     availableYears: Collection<int, int>,
     *     yearOfAdmission: ?int,
     *     students: Collection<int, Student>,
     *     studentId: ?int,
     *     selectedStudent: ?Student,
     *     approvalDate: string,
     *     isReady: bool,
     *     semesterBlocks: list<array{
     *         year: int,
     *         semester: int,
     *         session: string,
     *         courses: list<array{courseUnit: CourseUnit, score: ?Score}>,
     *         blankRowCount: int,
     *         maxUnit: int,
     *         unitPassed: int,
     *         gpa: ?float,
     *         cgpa: ?float,
     *     }>,
     *     finalCgpa: ?float,
     *     finalClassification: ?DiplomaClassification,
     *     approvalDateFormatted: string,
     * }
     */
    private function resolveResult(Request $request): array
    {
        $programmes = Programme::query()->orderBy('name')->get();

        $programmeId = $request->integer('programme_id') ?: null;
        $yearOfAdmission = $request->integer('year_of_admission') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $approvalDate = $request->string('approval_date')->toString() ?: now()->toDateString();

        $selectedProgramme = $programmeId ? $programmes->firstWhere('id', $programmeId) : null;

        $availableYears = $selectedProgramme
            ? Student::query()
                ->where('programme_id', $selectedProgramme->id)
                ->distinct()
                ->orderByDesc('year_of_admission')
                ->pluck('year_of_admission')
            : collect();

        $students = ($selectedProgramme && $yearOfAdmission)
            ? Student::query()
                ->where('programme_id', $selectedProgramme->id)
                ->where('year_of_admission', $yearOfAdmission)
                ->orderBy('matric_no')
                ->get()
            : collect();

        $selectedStudent = $studentId ? $students->firstWhere('id', $studentId) : null;

        $isReady = false;
        $semesterBlocks = [];
        $finalCgpa = null;
        $finalClassification = null;

        if ($selectedStudent && $yearOfAdmission) {
            $isReady = $selectedStudent->hasCompleteResult();

            if ($isReady) {
                $semesterBlocks = $this->buildSemesterBlocks($selectedStudent, $yearOfAdmission);
                $finalCgpa = $selectedStudent->cgpa();
                $finalClassification = $finalCgpa !== null ? DiplomaClassification::forCgpa($finalCgpa) : null;
            }
        }

        return [
            'programmes' => $programmes,
            'selectedProgramme' => $selectedProgramme,
            'programmeId' => $programmeId,
            'availableYears' => $availableYears,
            'yearOfAdmission' => $yearOfAdmission,
            'students' => $students,
            'studentId' => $studentId,
            'selectedStudent' => $selectedStudent,
            'approvalDate' => $approvalDate,
            'isReady' => $isReady,
            'semesterBlocks' => $semesterBlocks,
            'finalCgpa' => $finalCgpa,
            'finalClassification' => $finalClassification,
            'approvalDateFormatted' => $this->formatApprovalDate($approvalDate),
        ];
    }

    /**
     * Renders a date as "27TH APR, 2019" (ordinal day super-scripted, month
     * abbreviated, all caps) to match the sample statement of result.
     */
    private function formatApprovalDate(string $date): string
    {
        $carbon = Carbon::parse($date);
        $day = (int) $carbon->format('j');

        $suffix = match (true) {
            $day % 100 >= 11 && $day % 100 <= 13 => 'TH',
            $day % 10 === 1 => 'ST',
            $day % 10 === 2 => 'ND',
            $day % 10 === 3 => 'RD',
            default => 'TH',
        };

        return "{$day}<sup>{$suffix}</sup> ".strtoupper($carbon->format('M, Y'));
    }

    /**
     * Groups the student's programme's course units into (year, semester)
     * blocks — one per box on the printed transcript — each carrying its own
     * course rows, unit totals, and GPA/CGPA snapshot as of that point.
     *
     * @return list<array{
     *     year: int,
     *     semester: int,
     *     session: string,
     *     courses: list<array{courseUnit: CourseUnit, score: ?Score}>,
     *     blankRowCount: int,
     *     maxUnit: int,
     *     unitPassed: int,
     *     gpa: ?float,
     *     cgpa: ?float,
     * }>
     */
    private function buildSemesterBlocks(Student $student, int $yearOfAdmission): array
    {
        $courseUnits = CourseUnit::query()
            ->where('programme_id', $student->programme_id)
            ->orderBy('year')
            ->orderBy('semester')
            ->orderBy('code')
            ->get();

        $scoresByCourseUnitId = $student->scores()->get()->keyBy('course_unit_id');

        /**
         * @var array<string, array{
         *     year: int,
         *     semester: int,
         *     session: string,
         *     courses: list<array{courseUnit: CourseUnit, score: ?Score}>,
         *     blankRowCount: int,
         *     maxUnit: int,
         *     unitPassed: int,
         *     gpa: ?float,
         *     cgpa: ?float,
         * }> $blocks
         */
        $blocks = [];

        foreach ($courseUnits as $courseUnit) {
            $key = "{$courseUnit->year}-{$courseUnit->semester}";

            if (! isset($blocks[$key])) {
                $blocks[$key] = [
                    'year' => $courseUnit->year,
                    'semester' => $courseUnit->semester,
                    'session' => $this->sessionLabel($yearOfAdmission, $courseUnit->year),
                    'courses' => [],
                    'blankRowCount' => 0,
                    'maxUnit' => 0,
                    'unitPassed' => 0,
                    'gpa' => null,
                    'cgpa' => null,
                ];
            }

            /** @var ?Score $score */
            $score = $scoresByCourseUnitId->get($courseUnit->id);

            $blocks[$key]['courses'][] = ['courseUnit' => $courseUnit, 'score' => $score];
            $blocks[$key]['maxUnit'] += $courseUnit->credit_units;

            if ($score && $score->letter_grade !== 'F') {
                $blocks[$key]['unitPassed'] += $courseUnit->credit_units;
            }
        }

        $maxEstimatedLines = 0;
        foreach ($blocks as $block) {
            $maxEstimatedLines = max($maxEstimatedLines, $this->estimateBlockLines($block['courses']));
        }

        foreach ($blocks as $key => $block) {
            $blocks[$key]['gpa'] = $student->gpaFor($block['year'], $block['semester']);
            $blocks[$key]['cgpa'] = $student->cgpaThroughSemester($block['year'], $block['semester']);
            $blocks[$key]['blankRowCount'] = $maxEstimatedLines - $this->estimateBlockLines($block['courses']);
        }

        return array_values($blocks);
    }

    /**
     * Counts how many printed rows a block's course table will take, so
     * every box on the page can be padded with blank rows to a common
     * height. The course title column is wide enough that every title in
     * the catalogue prints on a single line, so this is just the course
     * count — no line-wrapping estimate needed.
     *
     * @param  list<array{courseUnit: CourseUnit, score: ?Score}>  $courses
     */
    private function estimateBlockLines(array $courses): int
    {
        return count($courses);
    }

    private function sessionLabel(int $yearOfAdmission, int $year): string
    {
        $start = $yearOfAdmission + ($year - 1);

        return "{$start}/".($start + 1);
    }

    private function exportFilename(Student $student): string
    {
        return Str::slug($student->name.'-transcript');
    }
}
