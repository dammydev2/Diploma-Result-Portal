@extends(backpack_view('blank'))

@section('content')
@include('admin.transcript-styles')
<style>
    {{-- Matches dompdf's bundled DejaVu Serif exactly, so the on-screen
         preview wraps text identically to the exported PDF instead of
         falling back to whatever serif font the browser/OS has. --}}
    @font-face {
        font-family: 'DejaVu Serif';
        src: url('{{ asset('fonts/dejavu-serif/DejaVuSerif.ttf') }}') format('truetype');
        font-weight: normal;
        font-style: normal;
    }

    @font-face {
        font-family: 'DejaVu Serif';
        src: url('{{ asset('fonts/dejavu-serif/DejaVuSerif-Bold.ttf') }}') format('truetype');
        font-weight: bold;
        font-style: normal;
    }

    @font-face {
        font-family: 'DejaVu Serif';
        src: url('{{ asset('fonts/dejavu-serif/DejaVuSerif-Italic.ttf') }}') format('truetype');
        font-weight: normal;
        font-style: italic;
    }

    @font-face {
        font-family: 'DejaVu Serif';
        src: url('{{ asset('fonts/dejavu-serif/DejaVuSerif-BoldItalic.ttf') }}') format('truetype');
        font-weight: bold;
        font-style: italic;
    }

    {{-- Printing (Cmd/Ctrl+P) otherwise prints the whole admin page — sidebar,
         breadcrumbs, page title, filter form, export button, footer — around
         a shrunken transcript. Hide that chrome directly (rather than relying
         on the theme's own .d-print-none, which needs its external
         stylesheet loaded) so .tr-page prints alone, full-size, exactly as
         previewed.

         The page's content sections carry Animate.css's "animated fadeIn"
         class, which starts its keyframe at opacity:0. On screen that's
         invisible because the animation finishes in a fraction of a second,
         but the print renderer captures that initial opacity:0 frame
         directly — silently blanking the entire printed page. Freezing the
         animation and forcing opacity:1 for print fixes that. --}}
    @media print {
        @page {
            size: A4 portrait;
            margin: 0;
        }

        .animated {
            animation: none !important;
            opacity: 1 !important;
        }

        .navbar-vertical,
        nav[aria-label="breadcrumb"],
        .header-operation,
        #tr-filter-form,
        #tr-export-actions,
        .app-footer {
            display: none !important;
        }

        .tr-page {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }
    }
</style>
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Transcript</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">View and download a student's full transcript</p>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ backpack_url('transcript') }}" id="tr-filter-form" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label" for="programme_id">Programme</label>
                            <select
                                class="form-select"
                                id="programme_id"
                                name="programme_id"
                                onchange="document.getElementById('year_of_admission').value = ''; document.getElementById('student_id').value = ''; document.getElementById('tr-filter-form').submit()"
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
                                onchange="document.getElementById('student_id').value = ''; document.getElementById('tr-filter-form').submit()"
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
                            <label class="form-label" for="student_id">Student</label>
                            <select
                                class="form-select"
                                id="student_id"
                                name="student_id"
                                onchange="document.getElementById('tr-filter-form').submit()"
                                @disabled(! $yearOfAdmission)
                            >
                                <option value="">Select a student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" @selected($studentId === $student->id)>
                                        {{ $student->matric_no }} — {{ $student->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="approval_date">Date of approval</label>
                            <input
                                type="date"
                                class="form-control"
                                id="approval_date"
                                name="approval_date"
                                value="{{ $approvalDate }}"
                                onchange="document.getElementById('tr-filter-form').submit()"
                                @disabled(! $selectedStudent)
                            >
                        </div>
                    </form>

                    @if (! $selectedStudent)
                        <p class="text-muted mb-0">Select a programme, year of admission, and student to view their transcript.</p>
                    @elseif (! $isReady)
                        <p class="text-muted mb-0">
                            {{ $selectedStudent->name }} does not have a complete result yet — every course unit in
                            their programme needs a recorded score before a transcript can be viewed or
                            downloaded.
                        </p>
                    @else
                        <div id="tr-export-actions" class="d-flex justify-content-end gap-2 mb-3 d-print-none">
                            <a
                                href="{{ route('page.transcript.export.pdf', ['programme_id' => $programmeId, 'year_of_admission' => $yearOfAdmission, 'student_id' => $studentId, 'approval_date' => $approvalDate]) }}"
                                class="btn btn-outline-danger btn-sm"
                            >
                                <i class="la la-file-pdf-o"></i> Export PDF
                            </a>
                        </div>

                        <div class="tr-preview-wrapper" style="overflow-x: auto;">
                            @include('admin.transcript-document', [
                                'backgroundSrc' => $transcriptBackgroundUrl,
                                'signatureSrc' => $signatureUrl,
                            ])
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
