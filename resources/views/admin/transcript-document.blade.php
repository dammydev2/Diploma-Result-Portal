{{--
    Shared Transcript document markup, included by both the on-screen preview
    (admin.transcript) and the PDF export (admin.transcript-pdf). Identical to
    admin.statement-of-result-document except each course row shows the
    student's raw numeric score instead of its derived letter grade. Expects:

    $selectedStudent, $selectedProgramme, $semesterBlocks, $finalCgpa,
    $finalClassification, $approvalDateFormatted, $registrarName,
    $registrarTitle, $alterationNote, $backgroundSrc, $signatureSrc
--}}
<div class="tr-page" style="background-image: url('{{ $backgroundSrc }}');">
    <div class="tr-info-box">
        <div class="tr-info-row">
            <div class="tr-label" style="width: 140px;">NAME:</div>
            <div class="tr-value" style="width: 265px;">{{ strtoupper($selectedStudent->name) }}</div>
            <div class="tr-label" style="width: 140px;">MATRIC NUMBER:</div>
            <div class="tr-value" style="width: 150px;">{{ $selectedStudent->matric_no }}</div>
        </div>
        <div class="tr-info-row">
            <div class="tr-label" style="width: 140px;">PROGRAMME:</div>
            <div class="tr-value" style="width: 265px;">{{ strtoupper($selectedProgramme->level->label()) }}</div>
            <div class="tr-label" style="width: 140px;">DATE:</div>
            <div class="tr-value" style="width: 150px;">{!! $approvalDateFormatted !!}</div>
        </div>
        <div class="tr-info-row">
            <div class="tr-label" style="width: 140px;">SPECIALIZATION:</div>
            <div class="tr-value" style="width: 555px;">{{ strtoupper($selectedProgramme->name) }}</div>
        </div>
    </div>

    @foreach (collect($semesterBlocks)->chunk(2) as $rowBlocks)
        <div class="tr-grid-row" style="margin-top: 10px; margin-bottom: 10px;">
            @foreach ($rowBlocks as $block)
                <div class="tr-grid-cell">
                    <div class="tr-semester-box">
                        <div class="tr-semester-title">
                            {{ $block['semester'] === 1 ? 'FIRST' : 'SECOND' }} SEMESTER {{ $block['session'] }} SESSION
                        </div>
                        <table class="tr-course-table">
                            <tr class="tr-course-header">
                                <td style="width: 12%;">COURSE CODE</td>
                                <td style="width: 65.5%;">TITLE</td>
                                <td class="tr-numeric" style="width: 9.8%;">UNIT</td>
                                <td class="tr-numeric" style="width: 12.7%;">SCORE</td>
                            </tr>
                            @foreach ($block['courses'] as $row)
                                <tr>
                                    <td style="width: 12%;">{{ $row['courseUnit']->code }}</td>
                                    <td style="width: 70.5%;">{{ strtoupper($row['courseUnit']->title) }}</td>
                                    <td class="tr-numeric" style="width: 7.8%;">{{ $row['courseUnit']->credit_units }}</td>
                                    <td class="tr-numeric" style="width: 9.7%;">{{ $row['score']?->score ?? '' }}</td>
                                </tr>
                            @endforeach
                            @for ($i = 0; $i < $block['blankRowCount']; $i++)
                                <tr>
                                    <td style="width: 12%;">&nbsp;</td>
                                    <td style="width: 70.5%;">&nbsp;</td>
                                    <td class="tr-numeric" style="width: 7.8%;">&nbsp;</td>
                                    <td class="tr-numeric" style="width: 9.7%;">&nbsp;</td>
                                </tr>
                            @endfor
                        </table>
                    </div>
                    <table class="tr-semester-footer">
                        <tr>
                            <td class="tr-label" style="width: 25.6%;">MAX UNIT:</td>
                            <td class="tr-value" style="width: 16.6%;">{{ $block['maxUnit'] }}</td>
                            <td class="tr-label tr-right" style="width: 24.7%;">GPA:</td>
                            <td class="tr-value" style="width: 33.1%;">{{ $block['gpa'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="tr-label" style="width: 25.6%;">UNIT PASSED:</td>
                            <td class="tr-value" style="width: 16.6%;">{{ $block['unitPassed'] }}</td>
                            <td class="tr-label tr-right" style="width: 24.7%;">CGPA:</td>
                            <td class="tr-value" style="width: 33.1%;">{{ $block['cgpa'] ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach

    <table class="tr-cumulative-box">
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

    <div class="tr-signature">
        <img src="{{ $signatureSrc }}" alt="Registrar's signature" class="tr-signature-img">
        <div class="tr-signature-line"></div>
        <div class="tr-registrar-name">{{ $registrarName }}</div>
        <div class="tr-registrar-title">{{ $registrarTitle }}</div>
    </div>

    <p class="tr-alteration-note">{{ $alterationNote }}</p>
</div>
