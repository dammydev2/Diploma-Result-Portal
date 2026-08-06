<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $selectedStudent->name }} - Transcript</title>
    @include('admin.transcript-styles')
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            background-image: url('{{ $transcriptBackgroundPath }}');
            background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: top left;
        }
    </style>
</head>
<body>
    @include('admin.transcript-document', [
        'backgroundSrc' => null,
        'signatureSrc' => $signaturePath,
    ])
</body>
</html>
