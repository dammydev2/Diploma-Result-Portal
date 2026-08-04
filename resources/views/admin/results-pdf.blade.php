<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $selectedProgramme->name }} - Semester Result</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        h1 {
            font-size: 16px;
            margin: 0;
            text-align: center;
        }

        p.address {
            margin: 2px 0 0;
            text-align: center;
            color: #4b5563;
        }

        p.programme {
            margin: 8px 0 0;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }

        p.title {
            margin: 2px 0 12px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #9ca3af;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        thead th {
            background-color: #f3f4f6;
        }

        td.matric {
            font-weight: bold;
            text-align: left;
        }

        .score {
            font-weight: bold;
        }

        .grade {
            color: #4b5563;
            font-size: 9px;
        }

        .gp {
            font-weight: bold;
        }

        p.footnote {
            margin-top: 14px;
            font-size: 9px;
            font-style: italic;
            color: #4b5563;
        }
    </style>
</head>
<body>
    <h1>{{ $institutionName }}</h1>
    @if ($institutionAddress)
        <p class="address">{{ $institutionAddress }}</p>
    @endif
    <p class="programme">
        {{ strtoupper($selectedProgramme->name) }} {{ $selectedProgramme->level->value }} {{ $year }}
    </p>
    <p class="title">
        {{ $session }} {{ strtoupper($selectedProgramme->name) }} {{ $semester === 1 ? 'FIRST' : 'SECOND' }} SEMESTER RESULT
    </p>

    <table>
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
                    <td class="matric">{{ $student->matric_no }}</td>
                    @foreach ($courseUnits as $courseUnit)
                        @php $score = $studentScores->get($courseUnit->id); @endphp
                        <td>
                            @if ($score)
                                <div class="score">{{ $score->score }}</div>
                                <div class="grade">{{ $score->weighting }} {{ $score->letter_grade }}</div>
                            @endif
                        </td>
                    @endforeach
                    <td class="gp">{{ $gpas[$student->id] ?? '' }}</td>
                    @if ($showCumulativeColumns)
                        <td class="gp">{{ $previousCgpas[$student->id] ?? '' }}</td>
                        <td class="gp">{{ $cgpas[$student->id] ?? '' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footnote">{{ $footnote }}</p>
</body>
</html>
