@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Students</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">Browse students by programme and year of admission</p>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ backpack_url('students') }}" id="students-filter-form" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="programme_id">Programme</label>
                            <select
                                class="form-select"
                                id="programme_id"
                                name="programme_id"
                                onchange="document.getElementById('year_of_admission').value = ''; document.getElementById('students-filter-form').submit()"
                            >
                                <option value="">Select a programme</option>
                                @foreach ($programmes as $programme)
                                    <option value="{{ $programme->id }}" @selected($programmeId === $programme->id)>
                                        {{ $programme->name }} ({{ $programme->level->value }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="year_of_admission">Year of admission</label>
                            <select
                                class="form-select"
                                id="year_of_admission"
                                name="year_of_admission"
                                onchange="document.getElementById('students-filter-form').submit()"
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
                    </form>

                    @if ($selectedProgramme && $yearOfAdmission)
                        @if ($students->isEmpty())
                            <p class="text-muted mb-0">No students found for this selection.</p>
                        @else
                            @php
                                $exportParams = ['programme_id' => $programmeId, 'year_of_admission' => $yearOfAdmission];
                            @endphp
                            <div class="d-flex justify-content-end gap-2 mb-3 d-print-none">
                                <a href="{{ route('page.students.export.pdf', $exportParams) }}" class="btn btn-outline-danger btn-sm">
                                    <i class="la la-file-pdf-o"></i> Export PDF
                                </a>
                                <a href="{{ route('page.students.export.excel', $exportParams) }}" class="btn btn-outline-success btn-sm">
                                    <i class="la la-file-excel-o"></i> Export Excel
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Matric no</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($students as $student)
                                            <tr>
                                                <td>{{ $student->matric_no }}</td>
                                                <td>{{ $student->name }}</td>
                                                <td>
                                                    @if ($student->isSuspended)
                                                        <span class="badge bg-danger-lt">Suspended</span>
                                                    @else
                                                        <span class="badge bg-success-lt">Active</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-top border-2">
                                            <td colspan="2" class="text-end" style="text-transform: none; font-size: 1.25rem; font-weight: 800;">TOTAL STUDENTS</td>
                                            <td style="font-size: 1.25rem; font-weight: 800;">{{ $totalStudents }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">Select a programme and year of admission to view students.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
