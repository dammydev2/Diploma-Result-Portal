@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Score Entry</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">Record scores for a cohort in a single course unit</p>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ backpack_url('scores') }}" id="scores-filter-form" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label" for="programme_id">Programme</label>
                            <select
                                class="form-select"
                                id="programme_id"
                                name="programme_id"
                                onchange="document.getElementById('year_of_admission').value = ''; document.getElementById('course_unit_id').value = ''; document.getElementById('scores-filter-form').submit()"
                            >
                                <option value="">Select a programme</option>
                                @foreach ($programmes as $programme)
                                    <option value="{{ $programme->id }}" @selected($programmeId === $programme->id)>
                                        {{ $programme->name }} ({{ $programme->level->value }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="year_of_admission">Year of admission</label>
                            <select
                                class="form-select"
                                id="year_of_admission"
                                name="year_of_admission"
                                onchange="document.getElementById('scores-filter-form').submit()"
                                @disabled(! $selectedProgramme)
                            >
                                <option value="">Select a year</option>
                                @foreach ($availableYears as $availableYear)
                                    <option value="{{ $availableYear }}" @selected($yearOfAdmission === $availableYear)>
                                        {{ $availableYear }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="course_unit_id">Course unit</label>
                            <select
                                class="form-select"
                                id="course_unit_id"
                                name="course_unit_id"
                                @disabled(! $selectedProgramme)
                            >
                                <option value="">Select a course unit</option>
                                @foreach ($courseUnits as $courseUnit)
                                    <option value="{{ $courseUnit->id }}" @selected($courseUnitId === $courseUnit->id)>
                                        {{ $courseUnit->code }} — {{ $courseUnit->title }} (Year {{ $courseUnit->year }}, Semester {{ $courseUnit->semester }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    @if ($selectedProgramme && $yearOfAdmission && $selectedCourseUnit)
                        @if ($students->isEmpty())
                            <p class="text-muted mb-0">No students found for this selection.</p>
                        @else
                            <form method="POST" action="{{ route('page.scores.store') }}">
                                @csrf
                                <input type="hidden" name="programme_id" value="{{ $programmeId }}">
                                <input type="hidden" name="year_of_admission" value="{{ $yearOfAdmission }}">
                                <input type="hidden" name="course_unit_id" value="{{ $courseUnitId }}">

                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table">
                                        <thead>
                                            <tr>
                                                <th>Matric no</th>
                                                <th>Name</th>
                                                <th>Score</th>
                                                <th>Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($students as $student)
                                                @php $existingScore = $existingScores->get($student->id); @endphp
                                                <tr>
                                                    <td>{{ $student->matric_no }}</td>
                                                    <td>{{ $student->name }}</td>
                                                    <td>
                                                        @if ($student->isSuspended)
                                                            <span class="badge bg-danger-lt">Suspended</span>
                                                        @elseif ($existingScore ? ! $canEditScores : ! $canEnterScores)
                                                            <input
                                                                type="number"
                                                                class="form-control form-control-sm"
                                                                style="max-width: 6rem;"
                                                                value="{{ $existingScore?->score }}"
                                                                disabled
                                                            >
                                                        @else
                                                            <input
                                                                type="number"
                                                                class="form-control form-control-sm"
                                                                style="max-width: 6rem;"
                                                                name="scores[{{ $student->id }}]"
                                                                min="0"
                                                                max="100"
                                                                value="{{ old('scores.'.$student->id, $existingScore?->score) }}"
                                                            >
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($existingScore)
                                                            <span class="badge bg-blue-lt">{{ $existingScore->letter_grade }} ({{ $existingScore->weighting }})</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <button type="submit" class="btn btn-primary">Save scores</button>
                            </form>
                        @endif
                    @else
                        <p class="text-muted mb-0">Select a programme, year of admission, and course unit to enter scores.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('after_styles')
        @basset('https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css')

        {{-- Tom Select's bundled theme is light-mode-only and hardcodes a dark
             text color, so it's unreadable against Tabler's dark background.
             These overrides read Tabler's own theme variables instead of
             hardcoding colors, so they track light/dark mode automatically. --}}
        <style>
            .ts-control,
            .ts-control input,
            .ts-dropdown {
                color: var(--tblr-body-color);
                background-color: var(--tblr-form-select-bg, var(--tblr-body-bg));
            }

            .ts-control {
                border-color: var(--tblr-border-color);
            }

            .ts-dropdown {
                border-color: var(--tblr-border-color);
            }

            .ts-dropdown .option {
                color: var(--tblr-body-color);
            }

            .ts-dropdown .option.active,
            .ts-dropdown .option:hover {
                color: var(--tblr-body-color);
                background-color: var(--tblr-secondary-bg);
            }
        </style>
    @endpush

    @push('after_scripts')
        @basset('https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                new TomSelect('#course_unit_id', {
                    create: false,
                    onChange: function () {
                        document.getElementById('scores-filter-form').submit();
                    },
                });
            });
        </script>
    @endpush
@endsection
