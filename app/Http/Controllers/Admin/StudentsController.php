<?php

namespace App\Http\Controllers\Admin;

use App\Models\Programme;
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
 * Class StudentsController
 *
 * @property-read CrudPanel $crud
 */
class StudentsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);

        return view('admin.students', [
            'title' => 'Students',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Students' => false,
            ],
            ...$filters,
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $filters = $this->resolveFilters($request);

        abort_unless($filters['selectedProgramme'] && $filters['yearOfAdmission'], 404);

        $pdf = Pdf::loadView('admin.students-pdf', $filters);

        return $pdf->download($this->exportFilename($filters).'.pdf');
    }

    public function exportExcel(Request $request): Response
    {
        $filters = $this->resolveFilters($request);

        abort_unless($filters['selectedProgramme'] && $filters['yearOfAdmission'], 404);

        $tempPath = tempnam(sys_get_temp_dir(), 'students').'.xlsx';

        $writer = new ExcelWriter;
        $writer->openToFile($tempPath);

        $headerStyle = new Style(fontBold: true);

        $writer->addRow(Row::fromValuesWithStyle(['Matric no', 'Name', 'Status'], $headerStyle));

        foreach ($filters['students'] as $student) {
            $writer->addRow(Row::fromValues([$student->matric_no, $student->name, $student->isSuspended ? 'Suspended' : 'Active']));
        }

        $writer->addRow(Row::fromValuesWithStyle(['', 'TOTAL STUDENTS', $filters['totalStudents']], $headerStyle));

        $writer->close();

        return response()->download($tempPath, $this->exportFilename($filters).'.xlsx')->deleteFileAfterSend();
    }

    /**
     * @return array{programmes: Collection<int, Programme>, selectedProgramme: ?Programme, programmeId: ?int, availableYears: Collection<int, int>, yearOfAdmission: ?int, students: Collection<int, Student>, totalStudents: int}
     */
    private function resolveFilters(Request $request): array
    {
        $programmes = Programme::query()->orderBy('name')->get();

        $programmeId = $request->integer('programme_id') ?: null;
        $yearOfAdmission = $request->integer('year_of_admission') ?: null;

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
                ->orderBy('name')
                ->get()
            : collect();

        return [
            'programmes' => $programmes,
            'selectedProgramme' => $selectedProgramme,
            'programmeId' => $programmeId,
            'availableYears' => $availableYears,
            'yearOfAdmission' => $yearOfAdmission,
            'students' => $students,
            'totalStudents' => $students->count(),
        ];
    }

    /**
     * @param  array{selectedProgramme: ?Programme, yearOfAdmission: ?int}  $filters
     */
    private function exportFilename(array $filters): string
    {
        return Str::slug($filters['selectedProgramme']->name
            .'-'.$filters['selectedProgramme']->level->value
            .'-'.$filters['yearOfAdmission']
            .'-students');
    }
}
