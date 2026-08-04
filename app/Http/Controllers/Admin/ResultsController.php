<?php

namespace App\Http\Controllers\Admin;

use App\Models\CourseUnit;
use App\Models\Programme;
use App\Models\Score;
use App\Models\Setting;
use App\Models\Student;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer as ExcelWriter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResultsController
 *
 * @property-read CrudPanel $crud
 */
class ResultsController extends Controller
{
    private const string FOOTNOTE = 'A blank score, or a score below 40 (grade F), is an automatic carry-over — the student must re-sit the course next session.';

    public function index(Request $request): View
    {
        $result = $this->resolveResult($request);

        return view('admin.results', [
            'title' => 'Semester Result',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Semester Result' => false,
            ],
            'footnote' => self::FOOTNOTE,
            'unscoredNote' => $this->unscoredNote($result['unscoredCourseUnits']),
            ...$this->institutionDetails(),
            ...$result,
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $result = $this->resolveResult($request);

        abort_unless($result['isReady'], 404);

        $pdf = Pdf::loadView('admin.results-pdf', [
            'footnote' => self::FOOTNOTE,
            ...$this->institutionDetails(),
            ...$result,
        ])
            ->setPaper('a4', 'landscape');

        return $pdf->download($this->exportFilename($result['selectedProgramme'], $result['session'], $result['semester']).'.pdf');
    }

    public function exportExcel(Request $request): Response
    {
        $result = $this->resolveResult($request);

        abort_unless($result['isReady'], 404);

        $tempPath = tempnam(sys_get_temp_dir(), 'result').'.xlsx';

        $writer = new ExcelWriter;
        $writer->openToFile($tempPath);

        $headerStyle = new Style(fontBold: true);

        $headerRow = ['Matric No'];
        foreach ($result['courseUnits'] as $courseUnit) {
            $headerRow[] = $courseUnit->code;
        }
        $headerRow[] = 'GP';
        if ($result['showCumulativeColumns']) {
            $headerRow[] = 'GPA';
            $headerRow[] = 'CGPA';
        }

        $writer->addRow(Row::fromValuesWithStyle($headerRow, $headerStyle));

        foreach ($result['students'] as $student) {
            /** @var Collection<int, Score> $studentScores */
            $studentScores = $result['scores']->get($student->id, collect());

            $row = [$student->matric_no];

            foreach ($result['courseUnits'] as $courseUnit) {
                $score = $studentScores->get($courseUnit->id);
                $row[] = $score ? "{$score->score} / {$score->weighting} {$score->letter_grade}" : '';
            }

            $row[] = $result['gpas'][$student->id] ?? '';
            if ($result['showCumulativeColumns']) {
                $row[] = $result['previousCgpas'][$student->id] ?? '';
                $row[] = $result['cgpas'][$student->id] ?? '';
            }

            $writer->addRow(Row::fromValues($row));
        }

        $writer->addRow(Row::fromValues(['']));
        $writer->addRow(Row::fromValues([self::FOOTNOTE]));

        $writer->close();

        return response()->download($tempPath, $this->exportFilename($result['selectedProgramme'], $result['session'], $result['semester']).'.xlsx')->deleteFileAfterSend();
    }

    /**
     * @return array{
     *     programmes: Collection<int, Programme>,
     *     selectedProgramme: ?Programme,
     *     programmeId: ?int,
     *     availableYears: Collection<int, int>,
     *     yearOfAdmission: ?int,
     *     year: ?int,
     *     semester: ?int,
     *     session: ?string,
     *     students: Collection<int, Student>,
     *     courseUnits: Collection<int, CourseUnit>,
     *     scores: Collection<array-key, Collection<array-key, Score>>,
     *     gpas: array<array-key, ?float>,
     *     isReady: bool,
     *     unscoredCourseUnits: Collection<int, CourseUnit>,
     *     showCumulativeColumns: bool,
     *     previousCgpas: array<array-key, ?float>,
     *     cgpas: array<array-key, ?float>,
     * }
     */
    private function resolveResult(Request $request): array
    {
        $programmes = Programme::query()->orderBy('name')->get();

        $programmeId = $request->integer('programme_id') ?: null;
        $yearOfAdmission = $request->integer('year_of_admission') ?: null;
        $year = $request->integer('year') ?: null;
        $semester = $request->integer('semester') ?: null;

        $selectedProgramme = $programmeId ? $programmes->firstWhere('id', $programmeId) : null;

        $availableYears = $selectedProgramme
            ? Student::query()
                ->where('programme_id', $selectedProgramme->id)
                ->distinct()
                ->orderByDesc('year_of_admission')
                ->pluck('year_of_admission')
            : collect();

        $courseUnits = ($selectedProgramme && $year && $semester)
            ? CourseUnit::query()
                ->where('programme_id', $selectedProgramme->id)
                ->where('year', $year)
                ->where('semester', $semester)
                ->orderBy('code')
                ->get()
            : collect();

        $students = ($selectedProgramme && $yearOfAdmission && $courseUnits->isNotEmpty())
            ? Student::query()
                ->where('programme_id', $selectedProgramme->id)
                ->where('year_of_admission', $yearOfAdmission)
                ->orderBy('matric_no')
                ->get()
            : collect();

        $scores = $this->fetchScores($students, $courseUnits);

        /** @var array<array-key, ?float> $gpaValues */
        $gpaValues = [];

        $isReady = false;

        foreach ($students as $student) {
            /** @var Collection<int, Score> $studentScores */
            $studentScores = $scores->get($student->id, collect());

            $totalPoints = 0.0;
            $totalCreditUnits = 0;

            foreach ($courseUnits as $courseUnit) {
                $score = $studentScores->get($courseUnit->id);

                if ($score) {
                    $totalPoints += (float) $score->weighting * $courseUnit->credit_units;
                    $totalCreditUnits += $courseUnit->credit_units;
                }
            }

            $gpaValues[$student->id] = $this->calculateGpa($totalPoints, $totalCreditUnits);

            if ($courseUnits->isNotEmpty() && $studentScores->count() === $courseUnits->count()) {
                $isReady = true;
            }
        }

        $previousSlot = ($year && $semester) ? $this->previousSlot($year, $semester) : null;

        /** @var array<array-key, ?float> $previousCgpas */
        $previousCgpas = [];

        /** @var array<array-key, ?float> $cgpas */
        $cgpas = [];

        if ($previousSlot) {
            [$previousYear, $previousSemester] = $previousSlot;

            foreach ($students as $student) {
                $previousCgpas[$student->id] = $student->cgpaThroughSemester($previousYear, $previousSemester);
                $cgpas[$student->id] = $student->cgpaThroughSemester($year, $semester);
            }
        }

        return [
            'programmes' => $programmes,
            'selectedProgramme' => $selectedProgramme,
            'programmeId' => $programmeId,
            'availableYears' => $availableYears,
            'yearOfAdmission' => $yearOfAdmission,
            'year' => $year,
            'semester' => $semester,
            'session' => ($yearOfAdmission && $year) ? $this->sessionLabel($yearOfAdmission, $year) : null,
            'students' => $students,
            'courseUnits' => $courseUnits,
            'scores' => $scores,
            'gpas' => $gpaValues,
            'isReady' => $isReady,
            'unscoredCourseUnits' => $this->unscoredCourseUnits($courseUnits, $scores),
            'showCumulativeColumns' => $previousSlot !== null,
            'previousCgpas' => $previousCgpas,
            'cgpas' => $cgpas,
        ];
    }

    /**
     * The chronologically preceding (year, semester) slot in the
     * programme, or null if the given slot is the very first one
     * (year 1, semester 1).
     *
     * @return array{0: int, 1: int}|null
     */
    private function previousSlot(int $year, int $semester): ?array
    {
        if ($semester === 2) {
            return [$year, 1];
        }

        return $year > 1 ? [$year - 1, 2] : null;
    }

    /**
     * Course units with no score recorded for any student — i.e. marks
     * haven't been entered for that course at all yet.
     *
     * @param  Collection<int, CourseUnit>  $courseUnits
     * @param  Collection<array-key, Collection<array-key, Score>>  $scores
     * @return Collection<int, CourseUnit>
     */
    private function unscoredCourseUnits(Collection $courseUnits, Collection $scores): Collection
    {
        $scoredCourseUnitIds = $scores
            ->flatMap(fn (Collection $studentScores): Collection => $studentScores->keys())
            ->unique();

        return $courseUnits
            ->reject(fn (CourseUnit $courseUnit): bool => $scoredCourseUnitIds->contains($courseUnit->id))
            ->values();
    }

    /**
     * @return array{institutionName: string, institutionAddress: ?string}
     */
    private function institutionDetails(): array
    {
        return [
            'institutionName' => Setting::get('institution_name', config('institution.name')),
            'institutionAddress' => Setting::get('institution_address', config('institution.address')),
        ];
    }

    private function calculateGpa(float $totalPoints, int $totalCreditUnits): ?float
    {
        if ($totalCreditUnits === 0) {
            return null;
        }

        return round($totalPoints / $totalCreditUnits, 2);
    }

    /**
     * @param  Collection<int, Student>  $students
     * @param  Collection<int, CourseUnit>  $courseUnits
     * @return Collection<array-key, Collection<array-key, Score>>
     */
    private function fetchScores(Collection $students, Collection $courseUnits): Collection
    {
        if ($students->isEmpty() || $courseUnits->isEmpty()) {
            return collect();
        }

        /** @var Collection<array-key, Collection<array-key, Score>> $grouped */
        $grouped = Score::query()
            ->whereIn('student_id', $students->pluck('id'))
            ->whereIn('course_unit_id', $courseUnits->pluck('id'))
            ->get()
            ->groupBy('student_id')
            ->map(fn (Collection $studentScores): Collection => $studentScores->keyBy('course_unit_id'));

        return $grouped;
    }

    /**
     * @param  Collection<int, CourseUnit>  $unscoredCourseUnits
     */
    private function unscoredNote(Collection $unscoredCourseUnits): ?string
    {
        if ($unscoredCourseUnits->isEmpty()) {
            return null;
        }

        return 'No scores have been entered yet for: '.$unscoredCourseUnits->pluck('code')->implode(', ').'.';
    }

    private function sessionLabel(int $yearOfAdmission, int $year): string
    {
        $start = $yearOfAdmission + ($year - 1);

        return "{$start}/".($start + 1);
    }

    private function exportFilename(?Programme $programme, ?string $session, ?int $semester): string
    {
        return Str::slug($programme?->name.'-'.$session.'-semester-'.$semester.'-result');
    }
}
