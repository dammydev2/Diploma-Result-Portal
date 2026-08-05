<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $selectedStudent->name }} - Statement of Result</title>
    @include('admin.statement-of-result-styles')
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            background-image: url('{{ $backgroundPath }}');
            background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: top left;
        }
    </style>
</head>
<body>
    @include('admin.statement-of-result-document', [
        'backgroundSrc' => null,
        'signatureSrc' => $signaturePath,
    ])
</body>
</html>
