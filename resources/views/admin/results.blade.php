@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Semester Result</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">View a full semester result for a programme's cohort</p>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ backpack_url('results') }}" id="results-filter-form" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label" for="programme_id">Programme</label>
                            <select
                                class="form-select"
                                id="programme_id"
                                name="programme_id"
                                onchange="document.getElementById('year_of_admission').value = ''; document.getElementById('year').value = ''; document.getElementById('semester').value = ''; document.getElementById('results-filter-form').submit()"
                            >
                                <option value="">Select a programme</option>
                                @foreach ($programmes as $programme)
                                    <option value="{{ $programme->id }}" @selected($programmeId === $programme->id)>
                                        {{ $programme->name }} ({{ $programme->level->value }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="year_of_admission">Year of admission</label>
                            <select
                                class="form-select"
                                id="year_of_admission"
                                name="year_of_admission"
                                onchange="document.getElementById('results-filter-form').submit()"
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

                        <div class="col-md-3">
                            <label class="form-label" for="year">Year</label>
                            <select
                                class="form-select"
                                id="year"
                                name="year"
                                onchange="document.getElementById('semester').value = ''; document.getElementById('results-filter-form').submit()"
                                @disabled(! $selectedProgramme)
                            >
                                <option value="">Select a year</option>
                                @if ($selectedProgramme)
                                    <option value="1" @selected($year === 1)>{{ $selectedProgramme->level->value }}1</option>
                                    <option value="2" @selected($year === 2)>{{ $selectedProgramme->level->value }}2</option>
                                @endif
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="semester">Semester</label>
                            <select
                                class="form-select"
                                id="semester"
                                name="semester"
                                onchange="document.getElementById('results-filter-form').submit()"
                                @disabled(! $year)
                            >
                                <option value="">Select a semester</option>
                                <option value="1" @selected($semester === 1)>First semester</option>
                                <option value="2" @selected($semester === 2)>Second semester</option>
                            </select>
                        </div>
                    </form>

                    @if ($selectedProgramme && $yearOfAdmission && $year && $semester)
                        @if ($courseUnits->isEmpty())
                            <p class="text-muted mb-0">No course units exist for this programme, year, and semester.</p>
                        @elseif ($students->isEmpty())
                            <p class="text-muted mb-0">No students found for this cohort.</p>
                        @elseif (! $isReady)
                            <p class="text-muted mb-0">
                                No complete result yet for this semester — enter all scores for at least one
                                student before it can be viewed or downloaded.
                            </p>
                            @if ($unscoredNote)
                                <p class="text-danger small mb-0"><em>{{ $unscoredNote }}</em></p>
                            @endif
                        @else
                            @php
                                $exportParams = ['programme_id' => $programmeId, 'year_of_admission' => $yearOfAdmission, 'year' => $year, 'semester' => $semester];
                            @endphp
                            <div class="d-flex justify-content-end gap-2 mb-3 d-print-none">
                                <a href="{{ route('page.results.export.pdf', $exportParams) }}" class="btn btn-outline-danger btn-sm">
                                    <i class="la la-file-pdf-o"></i> Export PDF
                                </a>
                                <a href="{{ route('page.results.export.excel', $exportParams) }}" class="btn btn-outline-success btn-sm">
                                    <i class="la la-file-excel-o"></i> Export Excel
                                </a>
                            </div>

                            <h3 class="mb-1 text-center">{{ $institutionName }}</h3>
                            @if ($institutionAddress)
                                <p class="text-center text-muted mb-1">{{ $institutionAddress }}</p>
                            @endif
                            <p class="text-center fw-bold mb-1">
                                {{ strtoupper($selectedProgramme->name) }} {{ $selectedProgramme->level->value }} {{ $year }}
                            </p>
                            <p class="text-center fw-bold mb-3">
                                {{ $session }} {{ strtoupper($selectedProgramme->name) }} {{ $semester === 1 ? 'FIRST' : 'SECOND' }} SEMESTER RESULT
                            </p>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Matric No</th>
                                            @foreach ($courseUnits as $courseUnit)
                                                <th>{{ $courseUnit->code }}</th>
                                            @endforeach
                                            <th>GP</th>
                                            @if ($showCumulativeColumns)
                                                <th>GPA</th>
                                                <th>CGPA</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($students as $student)
                                            @php $studentScores = $scores->get($student->id, collect()); @endphp
                                            <tr>
                                                <td class="fw-bold">{{ $student->matric_no }}</td>
                                                @foreach ($courseUnits as $courseUnit)
                                                    @php $score = $studentScores->get($courseUnit->id); @endphp
                                                    <td>
                                                        @if ($score)
                                                            <div class="fw-bold">{{ $score->score }}</div>
                                                            <div class="small text-muted">{{ $score->weighting }} {{ $score->letter_grade }}</div>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="fw-bold">{{ $gpas[$student->id] ?? '' }}</td>
                                                @if ($showCumulativeColumns)
                                                    <td class="fw-bold">{{ $previousCgpas[$student->id] ?? '' }}</td>
                                                    <td class="fw-bold">{{ $cgpas[$student->id] ?? '' }}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-muted small mt-3 mb-0"><em>{{ $footnote }}</em></p>
                        @endif
                    @else
                        <p class="text-muted mb-0">Select a programme, year of admission, year, and semester to view a result.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
