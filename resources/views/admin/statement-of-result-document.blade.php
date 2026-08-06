{{--
    Shared Statement of Result document markup, included by both the
    on-screen preview (admin.statement-of-result) and the PDF export
    (admin.statement-of-result-pdf). Expects:

    $selectedStudent, $selectedProgramme, $semesterBlocks, $finalCgpa,
    $finalClassification, $approvalDateFormatted, $registrarName,
    $registrarTitle, $alterationNote, $backgroundSrc, $signatureSrc
--}}
<div class="sor-page" style="background-image: url('{{ $backgroundSrc }}');">
    <div class="sor-info-box">
        <div class="sor-info-row">
            <div class="sor-label" style="width: 140px;">NAME:</div>
            <div class="sor-value" style="width: 265px;">{{ strtoupper($selectedStudent->name) }}</div>
            <div class="sor-label" style="width: 140px;">MATRIC NUMBER:</div>
            <div class="sor-value" style="width: 150px;">{{ $selectedStudent->matric_no }}</div>
        </div>
        <div class="sor-info-row">
            <div class="sor-label" style="width: 140px;">PROGRAMME:</div>
            <div class="sor-value" style="width: 265px;">{{ strtoupper($selectedProgramme->level->label()) }}</div>
            <div class="sor-label" style="width: 140px;">DATE:</div>
            <div class="sor-value" style="width: 150px;">{!! $approvalDateFormatted !!}</div>
        </div>
        <div class="sor-info-row">
            <div class="sor-label" style="width: 140px;">SPECIALIZATION:</div>
            <div class="sor-value" style="width: 555px;">{{ strtoupper($selectedProgramme->name) }}</div>
        </div>
    </div>

    @foreach (collect($semesterBlocks)->chunk(2) as $rowBlocks)
        <div class="sor-grid-row" style="margin-top: 10px; margin-bottom: 10px;">
            @foreach ($rowBlocks as $block)
                <div class="sor-grid-cell">
                    <div class="sor-semester-box">
                        <div class="sor-semester-title">
                            {{ $block['semester'] === 1 ? 'FIRST' : 'SECOND' }} SEMESTER {{ $block['session'] }} SESSION
                        </div>
                        <table class="sor-course-table">
                            <tr class="sor-course-header">
                                <td style="width: 12%;">COURSE CODE</td>
                                <td style="width: 65.5%;">TITLE</td>
                                <td class="sor-numeric" style="width: 9.8%;">UNIT</td>
                                <td class="sor-numeric" style="width: 12.7%;">GRADE</td>
                            </tr>
                            @foreach ($block['courses'] as $row)
                                <tr>
                                    <td style="width: 12%;">{{ $row['courseUnit']->code }}</td>
                                    <td style="width: 70.5%;">{{ strtoupper($row['courseUnit']->title) }}</td>
                                    <td class="sor-numeric" style="width: 7.8%;">{{ $row['courseUnit']->credit_units }}</td>
                                    <td class="sor-numeric" style="width: 9.7%;">{{ $row['score']?->letter_grade ?? '' }}</td>
                                </tr>
                            @endforeach
                            @for ($i = 0; $i < $block['blankRowCount']; $i++)
                                <tr>
                                    <td style="width: 12%;">&nbsp;</td>
                                    <td style="width: 70.5%;">&nbsp;</td>
                                    <td class="sor-numeric" style="width: 7.8%;">&nbsp;</td>
                                    <td class="sor-numeric" style="width: 9.7%;">&nbsp;</td>
                                </tr>
                            @endfor
                        </table>
                    </div>
                    <table class="sor-semester-footer">
                        <tr>
                            <td class="sor-label" style="width: 25.6%;">MAX UNIT:</td>
                            <td class="sor-value" style="width: 16.6%;">{{ $block['maxUnit'] }}</td>
                            <td class="sor-label sor-right" style="width: 24.7%;">GPA:</td>
                            <td class="sor-value" style="width: 33.1%;">{{ $block['gpa'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="sor-label" style="width: 25.6%;">UNIT PASSED:</td>
                            <td class="sor-value" style="width: 16.6%;">{{ $block['unitPassed'] }}</td>
                            <td class="sor-label sor-right" style="width: 24.7%;">CGPA:</td>
                            <td class="sor-value" style="width: 33.1%;">{{ $block['cgpa'] ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach

    <table class="sor-cumulative-box">
        <tr>
            <td style="width: 280px;">
                <div>CUMMULATIVE GPA: <strong>{{ $finalCgpa }}</strong></div>
                <div>FINAL GRADE/CLASS:: <strong>{{ strtoupper($finalClassification->classification ?? '') }}</strong></div>
            </td>
            <td style="width: 423px;">
                DATE OF APPROVAL BY THE ACADEMIC BOARD:
                <strong>{!! $approvalDateFormatted !!}</strong>
            </td>
        </tr>
    </table>

    <div class="sor-signature">
        <img src="{{ $signatureSrc }}" alt="Registrar's signature" class="sor-signature-img">
        <div class="sor-signature-line"></div>
        <div class="sor-registrar-name">{{ $registrarName }}</div>
        <div class="sor-registrar-title">{{ $registrarTitle }}</div>
    </div>

    <p class="sor-alteration-note">{{ $alterationNote }}</p>
</div>
