@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Courses</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">Browse course units by programme, year and semester</p>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ backpack_url('courses') }}" id="courses-filter-form" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label" for="programme_id">Programme</label>
                            <select
                                class="form-select"
                                id="programme_id"
                                name="programme_id"
                                onchange="document.getElementById('courses-filter-form').submit()"
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
                            <label class="form-label" for="year">Year</label>
                            <select
                                class="form-select"
                                id="year"
                                name="year"
                                onchange="document.getElementById('courses-filter-form').submit()"
                                @disabled(! $selectedProgramme)
                            >
                                <option value="">Select a year</option>
                                @if ($selectedProgramme)
                                    <option value="1" @selected($year === 1)>{{ $selectedProgramme->level->value }}1</option>
                                    <option value="2" @selected($year === 2)>{{ $selectedProgramme->level->value }}2</option>
                                @endif
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="semester">Semester</label>
                            <select
                                class="form-select"
                                id="semester"
                                name="semester"
                                onchange="document.getElementById('courses-filter-form').submit()"
                                @disabled(! $year)
                            >
                                <option value="">Select a semester</option>
                                <option value="1" @selected($semester === 1)>First semester</option>
                                <option value="2" @selected($semester === 2)>Second semester</option>
                            </select>
                        </div>
                    </form>

                    @if ($selectedProgramme && $year && $semester)
                        @if ($courseUnits->isEmpty())
                            <p class="text-muted mb-0">No courses found for this selection.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Title</th>
                                            <th>CU</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($courseUnits as $courseUnit)
                                            <tr>
                                                <td>{{ $courseUnit->code }}</td>
                                                <td>{{ $courseUnit->title }}</td>
                                                <td>{{ $courseUnit->credit_units }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">Select a programme, year and semester to view courses.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
