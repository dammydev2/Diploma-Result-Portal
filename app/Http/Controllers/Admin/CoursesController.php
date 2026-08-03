<?php

namespace App\Http\Controllers\Admin;

use App\Models\CourseUnit;
use App\Models\Programme;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class CoursesController
 *
 * @property-read CrudPanel $crud
 */
class CoursesController extends Controller
{
    public function index(Request $request): View
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

        return view('admin.courses', [
            'title' => 'Courses',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Courses' => false,
            ],
            'programmes' => $programmes,
            'selectedProgramme' => $selectedProgramme,
            'programmeId' => $programmeId,
            'year' => $year,
            'semester' => $semester,
            'courseUnits' => $courseUnits,
            'totalUnits' => $courseUnits->sum('credit_units'),
        ]);
    }
}
