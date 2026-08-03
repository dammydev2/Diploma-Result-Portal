<?php

namespace App\Http\Controllers\Admin;

use App\Models\CourseUnit;
use App\Models\Programme;
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
 * Class CoursesController
 *
 * @property-read CrudPanel $crud
 */
class CoursesController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);

        return view('admin.courses', [
            'title' => 'Courses',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Courses' => false,
            ],
            ...$filters,
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $filters = $this->resolveFilters($request);

        abort_unless($filters['selectedProgramme'] && $filters['year'] && $filters['semester'], 404);

        $pdf = Pdf::loadView('admin.courses-pdf', $filters);

        return $pdf->download($this->exportFilename($filters['selectedProgramme'], $filters['year'], $filters['semester']).'.pdf');
    }

    public function exportExcel(Request $request): Response
    {
        $filters = $this->resolveFilters($request);

        abort_unless($filters['selectedProgramme'] && $filters['year'] && $filters['semester'], 404);

        $tempPath = tempnam(sys_get_temp_dir(), 'course-units').'.xlsx';

        $writer = new ExcelWriter;
        $writer->openToFile($tempPath);

        $headerStyle = new Style(fontBold: true);

        $writer->addRow(Row::fromValuesWithStyle(['Code', 'Title', 'Credit units'], $headerStyle));

        foreach ($filters['courseUnits'] as $courseUnit) {
            $writer->addRow(Row::fromValues([$courseUnit->code, $courseUnit->title, $courseUnit->credit_units]));
        }

        $writer->addRow(Row::fromValuesWithStyle(['', 'TOTAL UNITS', $filters['totalUnits']], $headerStyle));

        $writer->close();

        return response()->download($tempPath, $this->exportFilename($filters['selectedProgramme'], $filters['year'], $filters['semester']).'.xlsx')->deleteFileAfterSend();
    }

    /**
     * @return array{programmes: Collection<int, Programme>, selectedProgramme: ?Programme, programmeId: ?int, year: ?int, semester: ?int, courseUnits: Collection<int, CourseUnit>, totalUnits: int}
     */
    private function resolveFilters(Request $request): array
    {
        $programmes = Programme::query()->orderBy('name')->get();

        $programmeId = $request->integer('programme_id') ?: null;
        $year = $request->integer('year') ?: null;
        $semester = $request->integer('semester') ?: null;

        $selectedProgramme = $programmeId ? $programmes->firstWhere('id', $programmeId) : null;

        $courseUnits = ($selectedProgramme && $year && $semester)
            ? CourseUnit::query()
                ->where('programme_id', $selectedProgramme->id)
                ->where('year', $year)
                ->where('semester', $semester)
                ->orderBy('code')
                ->get()
            : collect();

        return [
            'programmes' => $programmes,
            'selectedProgramme' => $selectedProgramme,
            'programmeId' => $programmeId,
            'year' => $year,
            'semester' => $semester,
            'courseUnits' => $courseUnits,
            'totalUnits' => $courseUnits->sum('credit_units'),
        ];
    }

    private function exportFilename(Programme $programme, int $year, int $semester): string
    {
        return Str::slug($programme->name.'-'.$programme->level->value.$year.'-semester-'.$semester);
    }
}
