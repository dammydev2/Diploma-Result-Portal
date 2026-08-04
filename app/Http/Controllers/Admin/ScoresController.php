<?php

namespace App\Http\Controllers\Admin;

use App\Models\CourseUnit;
use App\Models\Programme;
use App\Models\Score;
use App\Models\Student;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

/**
 * Class ScoresController
 *
 * @property-read CrudPanel $crud
 */
class ScoresController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);

        return view('admin.scores', [
            'title' => 'Score Entry',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Score Entry' => false,
            ],
            ...$filters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'programme_id' => ['required', 'exists:programmes,id'],
            'year_of_admission' => ['required', 'integer'],
            'course_unit_id' => ['required', 'exists:course_units,id'],
            'scores' => ['array'],
            'scores.*' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $courseUnit = CourseUnit::findOrFail((int) $validated['course_unit_id']);

        abort_unless($courseUnit->programme_id === (int) $validated['programme_id'], 422);

        $students = Student::query()
            ->where('programme_id', $validated['programme_id'])
            ->where('year_of_admission', $validated['year_of_admission'])
            ->get()
            ->keyBy('id');

        $saved = 0;

        foreach ($validated['scores'] ?? [] as $studentId => $rawScore) {
            if ($rawScore === null || $rawScore === '') {
                continue;
            }

            $student = $students->get((int) $studentId);

            if (! $student || $student->isSuspended) {
                continue;
            }

            Score::updateOrCreate(
                ['student_id' => $student->id, 'course_unit_id' => $courseUnit->id],
                ['score' => $rawScore]
            );

            $saved++;
        }

        \Alert::success("Saved {$saved} score(s).")->flash();

        return redirect()->route('page.scores.index', [
            'programme_id' => $validated['programme_id'],
            'year_of_admission' => $validated['year_of_admission'],
            'course_unit_id' => $validated['course_unit_id'],
        ]);
    }

    /**
     * @return array{programmes: Collection<int, Programme>, selectedProgramme: ?Programme, programmeId: ?int, availableYears: Collection<int, int>, yearOfAdmission: ?int, courseUnits: Collection<int, CourseUnit>, courseUnitId: ?int, selectedCourseUnit: ?CourseUnit, students: Collection<int, Student>, existingScores: Collection<int, Score>}
     */
    private function resolveFilters(Request $request): array
    {
        $programmes = Programme::query()->orderBy('name')->get();

        $programmeId = $request->integer('programme_id') ?: null;
        $yearOfAdmission = $request->integer('year_of_admission') ?: null;
        $courseUnitId = $request->integer('course_unit_id') ?: null;

        $selectedProgramme = $programmeId ? $programmes->firstWhere('id', $programmeId) : null;

        $availableYears = $selectedProgramme
            ? Student::query()
                ->where('programme_id', $selectedProgramme->id)
                ->distinct()
                ->orderByDesc('year_of_admission')
                ->pluck('year_of_admission')
            : collect();

        $courseUnits = $selectedProgramme
            ? CourseUnit::query()
                ->where('programme_id', $selectedProgramme->id)
                ->orderBy('year')
                ->orderBy('semester')
                ->orderBy('code')
                ->get()
            : collect();

        $selectedCourseUnit = $courseUnitId ? $courseUnits->firstWhere('id', $courseUnitId) : null;

        $students = ($selectedProgramme && $yearOfAdmission && $selectedCourseUnit)
            ? Student::query()
                ->where('programme_id', $selectedProgramme->id)
                ->where('year_of_admission', $yearOfAdmission)
                ->orderBy('name')
                ->get()
            : collect();

        $existingScores = $selectedCourseUnit && $students->isNotEmpty()
            ? Score::query()
                ->where('course_unit_id', $selectedCourseUnit->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id')
            : collect();

        return [
            'programmes' => $programmes,
            'selectedProgramme' => $selectedProgramme,
            'programmeId' => $programmeId,
            'availableYears' => $availableYears,
            'yearOfAdmission' => $yearOfAdmission,
            'courseUnits' => $courseUnits,
            'courseUnitId' => $courseUnitId,
            'selectedCourseUnit' => $selectedCourseUnit,
            'students' => $students,
            'existingScores' => $existingScores,
        ];
    }
}
