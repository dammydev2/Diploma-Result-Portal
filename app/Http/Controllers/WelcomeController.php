<?php

namespace App\Http\Controllers;

use App\Models\Programme;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Contracts\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        return view('welcome', [
            'institutionName' => Setting::get('institution_name', config('institution.name')),
            'institutionAddress' => Setting::get('institution_address', config('institution.address')),
            'totalProgrammes' => Programme::query()->count(),
            'totalStudents' => Student::query()->count(),
            'programmes' => Programme::query()->orderBy('name')->get(),
        ]);
    }
}
