<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $selectedProgramme->name }} - Students</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 0;
        }

        p.subheading {
            margin-top: 4px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }

        thead th {
            background-color: #f3f4f6;
        }

        tfoot td {
            font-weight: bold;
            font-size: 14px;
        }

        .text-end {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>{{ $selectedProgramme->name }} ({{ $selectedProgramme->level->value }})</h1>
    <p class="subheading">Year of admission: {{ $yearOfAdmission }}</p>

    <table>
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
                    <td>{{ $student->isSuspended ? 'Suspended' : 'Active' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-end">TOTAL STUDENTS</td>
                <td>{{ $totalStudents }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
